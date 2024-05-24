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
      const inputNamePrefix = '[data-formengine-input-name="data[tt_content][1][tx_dalleimage_prompt_'
      const selectNamePrefix = '[name="data[tt_content][1][tx_dalleimage_prompt_'

      const prompt = {
        description: document.querySelector(`${inputNamePrefix}description]`).value,
        subject: document.querySelector(`${inputNamePrefix}subject]`).value,
        style: document.querySelector(`${selectNamePrefix}style]`).value,
        colors: Array.from(document.querySelectorAll(`${selectNamePrefix}colors] option[selected]`), option => option.value),
        emotion: Array.from(document.querySelectorAll(`${selectNamePrefix}emotion] option[selected]`), option => option.value),
        composition: Array.from(document.querySelectorAll(`${selectNamePrefix}composition] option[selected]`), option => option.value),
        artworks: document.querySelector(`${selectNamePrefix}artworks]`).value,
        artists: document.querySelector(`${selectNamePrefix}artists]`).value,
        camera_proximity: document.querySelector(`${selectNamePrefix}camera_proximity]`).value,
        camera_position: document.querySelector(`${selectNamePrefix}camera_position]`).value,
        camera_lenses: document.querySelector(`${selectNamePrefix}camera_lenses]`).value,
        camera_shot: document.querySelector(`${selectNamePrefix}camera_shot]`).value,
        lighting: document.querySelector(`${selectNamePrefix}lighting]`).value,
        film_type: document.querySelector(`${selectNamePrefix}film_type]`).value
      }

      console.log(prompt)

      const finalPrompt = `
        ${(prompt.camera_shot !== '') ? `A ${prompt.camera_shot} of a ` : ''}
        ${(prompt.colors.length > 0) ? `${prompt.colors.join(', ')} ` : ''}
        ${(prompt.subject !== '') ? `${prompt.subject} ` : ''}
        ${(prompt.style !== '') ? `in the style of ${prompt.style}, ` : ''}
        ${(prompt.artworks !== '') ? `inspired by ${prompt.artworks} ` : ''}
        ${(prompt.artists !== '') ? `created by ${prompt.artists}. ` : ''}
        ${(prompt.emotion.length > 0) ? `This image should evoke a sense of ${prompt.emotion.join(', ')} and ` : ''}
        ${(prompt.composition.length > 0) ? `be composed with ${prompt.composition.join(', ')}. ` : ''}
        ${(prompt.camera_proximity !== '') ? `Capture it with a ${prompt.camera_proximity} ` : ''}
        ${(prompt.camera_position !== '') ? `${prompt.camera_position} shot ` : ''}
        ${(prompt.camera_lenses !== '') ? `using ${prompt.camera_lenses}. ` : ''}
        ${(prompt.lighting !== '') ? `Illuminate with ${prompt.lighting} ` : ''}
        ${(prompt.film_type !== '') ? `and consider using ${prompt.film_type} film for added effect.` : ''}
      `

      document.querySelector(`${inputName}description]`).value = finalPrompt

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
