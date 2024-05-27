'use strict'
/* eslint-disable no-undef */
require(['TYPO3/CMS/Ajax/AjaxRequest', 'TYPO3/CMS/DocumentService'], function (AjaxRequest, DocumentService) {
  requirejs(['jquery'], function ($) {
    DocumentService.ready().then(() => {
      $(document).ajaxComplete(function () {
        const generatePromptButton = document.getElementsByClassName('generatePrompt')
        const sendToDalleButton = document.getElementsByClassName('sendToDalle')
        const inputNamePrefix = '[name="data[tt_content][1][tx_dalleimage_prompt_'
        const formEngineNamePrefix = '[data-formengine-input-name="data[tt_content][1][tx_dalleimage_prompt_'
        const inputfieldList = 'colors,camera_position,subject,style,emotion,composition,artworks,artists,illustration,camera_position,camera_lenses,camera_shot,lighting,film_type,emotion,composition'

        // const selectfieldList = ''

        if (sendToDalleButton.length) {
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

          const getFinalPrompt = (prompt) => {
            return `${(prompt.illustration !== '') ? `A ${prompt.illustration} of a ` : 'A '}` +
            `${(prompt.colors) ? `${prompt.colors} ` : ''}` +
            `${(prompt.subject !== '') ? `${prompt.subject}` : ''}` +
            `${(prompt.style !== '') ? ` in the style of ${prompt.style}. ` : '. '}` +
            `${(prompt.artworks !== '') ? `Inspired by ${prompt.artworks}. ` : ''}` +
            `${(prompt.artists !== '') ? `Created by ${prompt.artists}. ` : ''}` +
            `${(prompt.emotion) ? `This image should evoke a sense of ${prompt.emotion}. ` : ''}` +
            `${(prompt.composition) ? `It should be composed with ${prompt.composition}. ` : ''}` +
            `${(prompt.camera_position !== '') ? `Capture it with a ${prompt.camera_position} ` : ''}` +
            `${(prompt.camera_lenses !== '') ? `${prompt.camera_lenses} ` : ''}` +
            `${(prompt.camera_shot !== '') ? `${prompt.camera_shot}. ` : '. '}` +
            `${(prompt.lighting !== '') ? `Illuminate with ${prompt.lighting}. ` : ''}` +
            `${(prompt.film_type !== '') ? `Consider using ${prompt.film_type} film for added effect.` : ''}`
          }

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

          require(['TYPO3/CMS/Event/RegularEvent'], function (RegularEvent) {
            new RegularEvent('click', function (e) {
              document.querySelector(`${inputNamePrefix}description]`).value = document.querySelector(`${formEngineNamePrefix}description]`).value = getFinalPrompt(prompt)
            }).bindTo(generatePromptButton[0])

            new RegularEvent('click', function (e) {
              new AjaxRequest(TYPO3.settings.ajaxUrls.sf_dalleimages_getDalleImage)
                .withQueryArguments({
                  input: getFinalPrompt(prompt), 
                  uid: getCurrentContentUid()
                })
                .get()
                .then(async function (response) {
                  const resolved = await response.resolve()
                  resolved.result && location.reload()
                })
            }).bindTo(sendToDalleButton[0])
          })
        }
      })
    })
  })
})
