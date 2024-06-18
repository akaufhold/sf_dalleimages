'use strict'

import '../Scss/backend.scss'
import {ProgressBar} from './progressBar'
import {finalPrompt} from './generatePrompt'
import {getCurrentContentUid, getFormElement, getTargetElement} from './helpers'

require('./sizeOptions')

var progressbarInstance // still use var for global purpose

/* eslint-disable no-undef */
require(['TYPO3/CMS/Ajax/AjaxRequest', 'TYPO3/CMS/DocumentService'], function (AjaxRequest, DocumentService) {
  requirejs(['jquery'], function ($) {
    DocumentService.ready().then(() => {
      $(document).on('ajaxComplete', function () { /* Prevent input values runtime error */
        const progressbar = document.getElementsByClassName('progressBar')[0]
        /* Initializing button constants and input list */
        const generatePromptButton = document.getElementsByClassName('generatePrompt')
        const sendToDalleButton = document.getElementsByClassName('sendToDalle')
        const inputfieldList = 'subject,colors,camera_position,style,emotion,composition,artworks,artists,illustration,camera_position,camera_lenses,camera_shot,lighting,film_type,emotion,composition'

        if (progressbar) {
          progressbarInstance = new ProgressBar(progressbar)
        };

        if (sendToDalleButton.length) {
          /* Create Prompt Object from input and select values */
          const prompt = {}
          inputfieldList.split(',').forEach((el) => {
            // console.log(document.querySelector(`${formEngineNamePrefix}${el}]"]`), document.querySelector(`${inputNamePrefix}${el}]"]`))
            const currentElement = getTargetElement(el)
            prompt[el] = currentElement && currentElement.value.replaceAll(',', ', ')

            require(['TYPO3/CMS/Event/RegularEvent'], function (RegularEvent) {
              new RegularEvent('change', function (e) {
                // selecting input and hidden fields holding the value
                const targetElement = getTargetElement(el)
                prompt[el] = targetElement.value.replaceAll(',', ', ')
                getFormElement(false, 'name', 'prompt', 'description').value = getFormElement(false, 'data-formengine-input-name', 'prompt', 'description').value = finalPrompt(prompt)
              }).bindTo(currentElement)
            })
          })

          /* Click Events for custom TCA Buttons */
          require(['TYPO3/CMS/Event/RegularEvent'], function (RegularEvent) {
            /* generate prompt when click on "Generate prompt" button */
            new RegularEvent('click', function (e) {
              getFormElement(false, 'name', 'prompt', 'description').value = getFormElement(false, 'data-formengine-input-name', 'prompt', 'description').value = finalPrompt(prompt)
            }).bindTo(generatePromptButton[0])
            /* process ajax request when click on "Get Image from Dalle" button */
            new RegularEvent('click', function (e) {
              progressbarInstance.setPbStatus('progress')
              const model = getTargetElement('model').value
              const size = getTargetElement('size').value
              const quality = getTargetElement('quality').value
              const amount = getTargetElement('amount').value
              console.log(model, size, quality, amount)

              new AjaxRequest(TYPO3.settings.ajaxUrls.sf_dalleimages_getDalleImage)
                .withQueryArguments({
                  input: finalPrompt(prompt),
                  model: model,
                  size: size,
                  quality: quality,
                  amount: amount,
                  uid: getCurrentContentUid()
                })
                .get()
                .then(async function (response) {
                  const resolved = await response.resolve()
                  progressbarInstance.setPbStatus('success')
                  // const someTabTriggerEl = document.querySelector('.nav-tabs').children[1].children[0] // Switching tabs with js since no solution found with URIBuilder
                  // resolved.result && someTabTriggerEl.click()
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
