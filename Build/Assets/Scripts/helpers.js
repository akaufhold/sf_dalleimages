/* Get content uid from url parameter or input field for new elements */
/* eslint-disable no-unused-vars */
export const getCurrentContentUid = () => {
  let uid = 0
  const queryString = window.location.search
  const urlParams = new URLSearchParams(queryString)
  urlParams.forEach((name, val) => {
    name === 'edit' && (uid = val.split('edit[tt_content][')[1].split(']')[0])
  })
  return uid || document.querySelectorAll("[name^='data[tt_content]'][name$='[pid]']")[0].name.split('data[tt_content][')[1].split('][pid]')[0]
}

/* get typo3 form elements with atrributes */
export const getFormElement = (element, attrName, group, field) => {
  const elementString = element || ''
  const groupString = group ? '_' + group + '_' : '_'
  const query = `${elementString}[${attrName}="data[tt_content][${getCurrentContentUid()}][tx_dalleimage${groupString}${field}]"]`
  return document.querySelector(query)
}

/* returns the element with the first existing selector */
export const getTargetElement = (el) => {
  const element =
    getFormElement(false, 'name', false, el) ??
    getFormElement(false, 'name', 'prompt', el) ??
    getFormElement('select', 'data-formengine-input-name', 'prompt', el) ??
    getFormElement(false, 'data-formengine-input-name', 'prompt', el)

  return element
}
