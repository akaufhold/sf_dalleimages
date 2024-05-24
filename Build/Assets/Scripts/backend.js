'use strict'
/* eslint-disable no-undef */
require(['TYPO3/CMS/Ajax/AjaxRequest'], function (AjaxRequest) {
  requirejs(['jquery'], function ($) {
    $(document).ready(() => {
      const getCurrentContentUid = () => {
        let uid = 0
        const queryString = window.location.search
        const urlParams = new URLSearchParams(queryString)
        urlParams.forEach((name, val) => {
          console.log(name, val, name.includes('edit'))
          name === 'edit' && (uid = val.split('edit[tt_content][')[1].split(']')[0])
        })
        return uid
      }

      const sendToDalleButton = document.getElementsByClassName('sendToDalle')
      const inputName = '[data-formengine-input-name="data[tt_content][1][tx_dalleimage_prompt_'
      const prompt = {
        subject: document.querySelectorAll(`${inputName}_subject]"]`)[0],
        description: document.querySelectorAll(`${inputName}_description]"]`)[0],
        style: document.querySelectorAll(`${inputName}_style]"]`)[0],
        colors: document.querySelectorAll(`${inputName}_colors]"]`)[0],
        emotion: document.querySelectorAll(`${inputName}_emotion]"]`)[0],
        composition: document.querySelectorAll(`${inputName}_composition]"]`)[0]
      }

      sendToDalleButton &&
      sendToDalleButton[0].addEventListener('click', () => {
        const textPrompt = prompt.description.value
        // Generate a random number between 1 and 32
        new AjaxRequest(TYPO3.settings.ajaxUrls.sf_dalleimages_getDalleImage)
          .withQueryArguments({input: textPrompt, uid: getCurrentContentUid()})
          .get()
          .then(async function (response) {
            const resolved = await response.resolve()
            resolved.result && location.reload()
          })
      })
    })
  })
})
