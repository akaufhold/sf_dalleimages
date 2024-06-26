'use strict'

import '../Scss/backend.scss'
import {ProgressBar} from './progressBar'
import {finalPrompt} from './generatePrompt'
import {getCurrentContentUid, getFormElement, getTargetElement} from './helpers'

import AjaxRequest from '@typo3/core/ajax/ajax-request.js'
import DocumentService from '@typo3/core/document-service.js'
import RegularEvent from '@typo3/core/event/regular-event.js'

/* eslint-disable no-var */
var progressbarInstance // use var for global purpose

/* eslint-disable no-undef */
DocumentService.ready().then(() => {
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
      const currentElement = getTargetElement(el)
      prompt[el] = currentElement && currentElement.value.replaceAll(',', ', ')

      new RegularEvent('change', function (e) {
        // selecting input and hidden fields holding the value
        const targetElement = getTargetElement(el)
        prompt[el] = targetElement.value.replaceAll(',', ', ')
        getFormElement(false, 'name', 'prompt', 'description').value = getFormElement(false, 'data-formengine-input-name', 'prompt', 'description').value = finalPrompt(prompt)
      }).bindTo(currentElement)
    })

    /* Click Events for custom TCA Buttons */
    /* generate prompt when click on "Generate prompt" button */
    new RegularEvent('click', function (e) {
      getFormElement(false, 'name', 'prompt', 'description').value = getFormElement(false, 'data-formengine-input-name', 'prompt', 'description').value = finalPrompt(prompt)
    }).bindTo(generatePromptButton[0])
    /* process ajax request when click on "Get Image from Dalle" button */
    new RegularEvent('click', function (e) {
      const model = getTargetElement('model').value
      const size = getTargetElement('size').value
      const quality = getTargetElement('quality').value
      const amount = getTargetElement('amount').value
      progressbarInstance.setPbStatus('progress')

      new AjaxRequest(TYPO3.settings.ajaxUrls.sf_dalleimages_getDalleImage)
        .withQueryArguments({input: finalPrompt(prompt), model, size, quality, amount, uid: getCurrentContentUid()})
        .get()
        .then(async function (response) {
          const resolved = await response.resolve()
          const saveButton = document.querySelector('button[name="_savedok"]')
          progressbarInstance.setPbStatus('success')

          if (resolved.result) {
            // save form after image has being generated or reload frame
            saveButton ? saveButton.click() : location.reload()
          }
        })
    }).bindTo(sendToDalleButton[0])
  }
})
