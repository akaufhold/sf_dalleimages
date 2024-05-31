'use strict'

import '../Scss/backend.scss'
import {ProgressBar} from './progressBar'

var progressbarInstance // still use var for global purpose

/* Get content uid from url parameter */
/* eslint-disable no-unused-vars */
const getCurrentContentUid = () => {
  let uid = 0
  const queryString = window.location.search
  const urlParams = new URLSearchParams(queryString)
  urlParams.forEach((name, val) => {
    name === 'edit' && (uid = val.split('edit[tt_content][')[1].split(']')[0])
  })
  return uid
}

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
  `${(prompt.camera_shot !== '') ? `${prompt.camera_shot}. ` : ''}` +
  `${(prompt.lighting !== '') ? `Illuminate with ${prompt.lighting}. ` : ''}` +
  `${(prompt.film_type !== '') ? `Consider using ${prompt.film_type} film for added effect.` : ''}`
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

        /* Initializing button constants and input name prefixes */
        const generatePromptButton = document.getElementsByClassName('generatePrompt')
        const sendToDalleButton = document.getElementsByClassName('sendToDalle')
        const inputNamePrefix = '[name="data[tt_content][' + getCurrentContentUid() + '][tx_dalleimage_prompt_'
        const formEngineNamePrefix = '[data-formengine-input-name="data[tt_content][' + getCurrentContentUid() + '][tx_dalleimage_prompt_'
        const inputfieldList = 'subject,colors,camera_position,style,emotion,composition,artworks,artists,illustration,camera_position,camera_lenses,camera_shot,lighting,film_type,emotion,composition'

        if (sendToDalleButton.length) {
          /* Create Prompt Object from input and select values */
          const prompt = {}
          inputfieldList.split(',').forEach((el) => {
            const currentElement = document.querySelector(`${inputNamePrefix}${el}]"]`)
            prompt[el] = currentElement.value.replaceAll(',', ', ')

            require(['TYPO3/CMS/Event/RegularEvent'], function (RegularEvent) {
              // console.log('SELECT')
              new RegularEvent('change', function (e) {
                prompt[el] = currentElement.value.replaceAll(',', ', ')
                document.querySelector(`${inputNamePrefix}description]`).value = document.querySelector(`${formEngineNamePrefix}description]`).value = getFinalPrompt(prompt)
              }).bindTo(currentElement)
            })
          })

          /* Click Events for custom TCA Buttons */
          require(['TYPO3/CMS/Event/RegularEvent'], function (RegularEvent) {
            /* generate prompt when click on "Generate prompt" button */
            new RegularEvent('click', function (e) {
              document.querySelector(`${inputNamePrefix}description]`).value = document.querySelector(`${formEngineNamePrefix}description]`).value = getFinalPrompt(prompt)
            }).bindTo(generatePromptButton[0])
            /* process ajax request when click on "Get Image from Dalle" button */
            new RegularEvent('click', function (e) {
              progressbarInstance.setPbStatus('progress')
              new AjaxRequest(TYPO3.settings.ajaxUrls.sf_dalleimages_getDalleImage)
                .withQueryArguments({
                  backendFormUrl: window.location.origin + window.location.pathname,
                  input: getFinalPrompt(prompt),
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
