'use strict'

import '../Scss/backend.scss'
import {ProgressBar} from './progressBar'

var progressbarInstance // use var for global purpose

/* Get content uid from url parameter or input field for new elements */
/* eslint-disable no-unused-vars */
const getCurrentContentUid = () => {
  let uid = 0
  const queryString = window.location.search
  const urlParams = new URLSearchParams(queryString)
  urlParams.forEach((name, val) => {
    name === 'edit' && (uid = val.split('edit[tt_content][')[1].split(']')[0])
  })
  return uid || document.querySelectorAll("[name^='data[tt_content]'][name$='[pid]']")[0].name.split('data[tt_content][')[1].split('][pid]')[0]
}

console.log(getCurrentContentUid())
/* Generate text prompt for dalle image api call */
const getFinalPrompt = (prompt) => {
  return `${(prompt.illustration !== '') ? `A ${prompt.illustration} of a ` : 'A '}` +
  `${(prompt.colors) ? `${prompt.colors} ` : ''}` +
  `${(prompt.subject !== '') ? `${prompt.subject}` : ''}` +
  `${(prompt.style !== '') ? ` in the style of ${prompt.style}. ` : '. '}` +
  `${(prompt.artworks !== '') ? `Inspired by ${prompt.artworks}. ` : ''}` +
  `${(prompt.artists !== '') ? `Created by ${prompt.artists}. ` : ''}` +
  `${(prompt.emotion) ? `This image should evoke a sense of ${prompt.emotion}. ` : ''}` +
  `${(prompt.composition) ? `It's composition should be ${prompt.composition}. ` : ''}` +
  `${(prompt.camera_position !== '') ? `Capture it from a ${prompt.camera_position}. ` : ''}` +
  `${(prompt.camera_lenses !== '') ? `Use ${prompt.camera_lenses}. ` : ''}` +
  `${(prompt.camera_shot !== '') ? `The camera shot should be a ${prompt.camera_shot}. ` : ''}` +
  `${(prompt.lighting !== '') ? `Illuminate with ${prompt.lighting}. ` : ''}` +
  `${(prompt.film_type !== '') ? `Consider using ${prompt.film_type} film for added effect.` : ''}`
}

/* get typo3 form elements with atrributes */
const getFormElement = (element, attrName, group, field) => {
  const elementString = element || ''
  const groupString = group ? '_' + group + '_' : '_'
  const query = `${elementString}[${attrName}="data[tt_content][${getCurrentContentUid()}][tx_dalleimage${groupString}${field}]"]`
  // console.log(query)
  return document.querySelector(query)
}

/* returns the element with the first existing selector */
const getTargetElement = (el) => {
  const element =
    getFormElement(false, 'name', false, el) ??
    getFormElement(false, 'name', 'prompt', el) ??
    getFormElement('select', 'data-formengine-input-name', 'prompt', el) ??
    getFormElement(false, 'data-formengine-input-name', 'prompt', el)

  return element
}

/* eslint-disable no-undef */
require(['TYPO3/CMS/Ajax/AjaxRequest', 'TYPO3/CMS/DocumentService'], function (AjaxRequest, DocumentService) {
  requirejs(['jquery'], function ($) {
    DocumentService.ready().then(() => {
      $(document).on('ajaxComplete', function () { /* Prevent input values runtime error */
        const progressbar = document.getElementsByClassName('progressBar')[0]
        if (progressbar) {
          progressbarInstance = new ProgressBar(progressbar)
        };

        /* Initializing button constants and input list */
        const generatePromptButton = document.getElementsByClassName('generatePrompt')
        const sendToDalleButton = document.getElementsByClassName('sendToDalle')
        const inputfieldList = 'subject,colors,camera_position,style,emotion,composition,artworks,artists,illustration,camera_position,camera_lenses,camera_shot,lighting,film_type,emotion,composition'

        if (sendToDalleButton.length) {
          /* Create Prompt Object from input and select values */
          const prompt = {}
          inputfieldList.split(',').forEach((el) => {
            // console.log(document.querySelector(`${formEngineNamePrefix}${el}]"]`), document.querySelector(`${inputNamePrefix}${el}]"]`))
            console.log(el)
            const currentElement = getTargetElement(el)
            prompt[el] = currentElement && currentElement.value.replaceAll(',', ', ')

            require(['TYPO3/CMS/Event/RegularEvent'], function (RegularEvent) {
              new RegularEvent('change', function (e) {
                // selecting input and hidden fields holding the value
                const targetElement = getTargetElement(el)
                prompt[el] = targetElement.value.replaceAll(',', ', ')
                getFormElement(false, 'name', 'prompt', 'description').value = getFormElement(false, 'data-formengine-input-name', 'prompt', 'description').value = getFinalPrompt(prompt)
              }).bindTo(currentElement)
            })
          })

          /* Click Events for custom TCA Buttons */
          require(['TYPO3/CMS/Event/RegularEvent'], function (RegularEvent) {
            /* generate prompt when click on "Generate prompt" button */
            new RegularEvent('click', function (e) {
              getFormElement(false, 'name', 'prompt', 'description').value = getFormElement(false, 'data-formengine-input-name', 'prompt', 'description').value = getFinalPrompt(prompt)
            }).bindTo(generatePromptButton[0])
            /* process ajax request when click on "Get Image from Dalle" button */
            new RegularEvent('click', function (e) {
              progressbarInstance.setPbStatus('progress')
              const model = getFormElement(false, 'name', false, 'model').value
              const size = getFormElement(false, 'name', false, 'size').value
              const quality = getFormElement(false, 'name', false, 'quality').value
              const amount = getFormElement(false, 'name', false, 'amount').value

              new AjaxRequest(TYPO3.settings.ajaxUrls.sf_dalleimages_getDalleImage)
                .withQueryArguments({
                  backendFormUrl: window.location.origin + window.location.pathname,
                  input: getFinalPrompt(prompt),
                  model: model,
                  size: size,
                  quality: quality,
                  amount: amount,
                  uid: getCurrentContentUid()
                })
                .get()
                .then(async function (response) {
                  const resolved = await response.resolve()
                  const someTabTriggerEl = document.querySelector('.nav-tabs').children[1].children[0]
                  progressbarInstance.setPbStatus('success')
                  // resolved.result && someTabTriggerEl.click() // Switching tabs with js since no solution found with URIBuilder
                  const saveButton = document.querySelector('button[name="_savedok"]')

                  if (resolved.result) {
                    // save form after image has being generated or reload frame
                    saveButton ? saveButton.click() : location.reload()
                  }
                })
            }).bindTo(sendToDalleButton[0])
          })
        }
      })
    })
  })
})
