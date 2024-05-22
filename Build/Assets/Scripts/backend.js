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
        const uid = urlParams.get('edit[tt_content]')
        console.log(uid)
        // Generate a random number between 1 and 32
        new AjaxRequest(TYPO3.settings.ajaxUrls.sf_dalleimages_getDalleImage)
          .withQueryArguments({input: textPrompt, uid: uid})
          .get()
          .then(async function (response) {
            const resolved = JSON.decode(await response.resolve())
            console.log(resolved)
          })
      })
    })
  })
})
