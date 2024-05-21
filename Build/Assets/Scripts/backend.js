'use strict'
/* eslint-disable no-undef */
require(['TYPO3/CMS/Ajax/AjaxRequest'], function (AjaxRequest) {
  requirejs(['jquery'], function ($) {
    $(document).ready(() => {
      // const sendToDalleButton = document.getElementsByClassName('sendToDalle')[0]
      const imagePrompt = document.querySelectorAll('[data-formengine-input-name="data[tt_content][1][tx_dalleimage_prompt]"]')
      document.getElementsByClassName('sendToDalle') &&
      document.getElementsByClassName('sendToDalle')[0].addEventListener('click', () => {
        const textPrompt = imagePrompt[0].value
        // Generate a random number between 1 and 32
        new AjaxRequest(TYPO3.settings.ajaxUrls.sf_dalleimages_getDalleImage)
          .withQueryArguments({input: textPrompt})
          .get()
          .then(async function (response) {
            const resolved = await response.resolve()
            console.log(resolved.result)
          })
      })
    })
  })
})
