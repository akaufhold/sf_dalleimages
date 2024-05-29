'use strict'

import '../Scss/backend.scss'

var progressbarInstance // still use var for global purpose

class ProgressBar {
  progressBar
  constructor (progressBar) {
    this.progressbar = progressBar
    this.init()
  }

  init () {
    /* PROGRESS BAR */
    this.counterContainer = this.progressbar.getElementsByClassName('counterContainer')[0]
  }

  /* PROGRESS BAR */
  pbReset () {
    this.counterContainer.querySelector('.counterAmount').style.width = '0%'
    this.counterContainer.classList.remove('progress', 'error', 'success')
    this.counterContainer.querySelector('.errorMessage').innerHTML = 'error'
  }

  /* SET PROGRESS BAR STATUS */
  setPbStatus (status) {
    this.pbReset()
    this.counterContainer.classList.add(status)
    if ((status === 'success') || (status === 'error')) {
      this.counterContainer.querySelector('.counterAmount').style.width = '100%'
    }
  }

  /* ERROR HANDLING */
  errorHandling (errorMessage) {
    this.setPbStatus('error')
    this.progressbar.querySelector('.errorMessage').append(': ' + errorMessage.substring(0, 130))
  }
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
        const inputNamePrefix = '[name="data[tt_content][1][tx_dalleimage_prompt_'
        const formEngineNamePrefix = '[data-formengine-input-name="data[tt_content][1][tx_dalleimage_prompt_'
        const inputfieldList = 'colors,camera_position,subject,style,emotion,composition,artworks,artists,illustration,camera_position,camera_lenses,camera_shot,lighting,film_type,emotion,composition'

        if (sendToDalleButton.length) {
          /* Create Prompt Object from input and select values */
          const prompt = {}
          inputfieldList.split(',').forEach((el) => {
            prompt[el] = document.querySelector(`${inputNamePrefix}${el}]"]`).value.replaceAll(',', ', ')
            require(['TYPO3/CMS/Event/RegularEvent'], function (RegularEvent) {
              new RegularEvent('change', function (e) {
                prompt[el] = document.querySelector(`${inputNamePrefix}${el}]"]`).value.replaceAll(',', ', ')
                document.querySelector(`${inputNamePrefix}description]`).value = document.querySelector(`${formEngineNamePrefix}description]`).value = getFinalPrompt(prompt)
              }).bindTo(document.querySelector(`${inputNamePrefix}${el}]"]`))
            })
          })

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
            `${(prompt.camera_shot !== '') ? `${prompt.camera_shot}. ` : '. '}` +
            `${(prompt.lighting !== '') ? `Illuminate with ${prompt.lighting}. ` : ''}` +
            `${(prompt.film_type !== '') ? `Consider using ${prompt.film_type} film for added effect.` : ''}`
          }

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

          /* Click Events for custom TCA Buttons */
          require(['TYPO3/CMS/Event/RegularEvent'], function (RegularEvent) {
            new RegularEvent('click', function (e) {
              document.querySelector(`${inputNamePrefix}description]`).value = document.querySelector(`${formEngineNamePrefix}description]`).value = getFinalPrompt(prompt)
            }).bindTo(generatePromptButton[0])

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
                  // resolved.result && location.reload()
                })
            }).bindTo(sendToDalleButton[0])
          })
        }
      })
    })
  })
})
