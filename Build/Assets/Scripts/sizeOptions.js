require(['TYPO3/CMS/Ajax/AjaxRequest', 'TYPO3/CMS/DocumentService'], function (AjaxRequest, DocumentService) {
  /* eslint-disable no-undef */
  requirejs(['jquery'], function ($) {
    DocumentService.ready().then(() => {
      $(document).on('ajaxComplete', function () {
        const modelField = document.querySelector('[name$="[tx_dalleimage_model]"]')
        const sizeField = document.querySelector('[name$="[tx_dalleimage_size]"]')

        if (modelField && sizeField) {
          modelField.addEventListener('change', function () {
            const selectedModel = modelField.value
            let defaultSize = ''

            switch (selectedModel) {
              case 'dall-e-1':
                defaultSize = '256x256'
                break
              case 'dall-e-2':
                defaultSize = '512x512'
                break
              case 'dall-e-3':
                defaultSize = '1024x1024'
                break
            }
            // Set the default value for the size field
            sizeField.value = defaultSize
          })
        }
      })
    })
  })
})