import * as pluralize from 'pluralize'

import {capitalize, indefiniteArticle} from './utilities'

/* Generate text prompt sending to dalle api call */
export const finalPrompt = (prompt) => {
  /* Check if and which article (a, an) is needed */
  const article = pluralize.isPlural(prompt.subject) ? '' : `${indefiniteArticle(prompt.colors + prompt.subject)} `
  /* Check if illustration and article is empty for writing color in uppercase */
  const colors = `${(prompt.colors) ? `${(article === '' && prompt.illustration === '') ? prompt.colors.charAt(0).toUpperCase() + prompt.colors.slice(1) : prompt.colors} ` : ''}`
  return `${(prompt.illustration !== '') ? `${capitalize(indefiniteArticle(prompt.illustration))} ${prompt.illustration} of ${article}` : `${article} `}` +
    colors +
    `${(prompt.subject !== '') ? `${prompt.subject}` : ''}` +
    `${(prompt.style !== '') ? ` in the style of ${prompt.style}. ` : '. '}` +
    `${(prompt.artworks !== '') ? `Inspired by ${prompt.artworks}. ` : ''}` +
    `${(prompt.artists !== '') ? `Created by ${prompt.artists}. ` : ''}` +
    `${(prompt.emotion) ? `This image should evoke a sense of ${prompt.emotion}. ` : ''}` +
    `${(prompt.composition) ? `It's composition should be ${prompt.composition}. ` : ''}` +
    `${(prompt.camera_position !== '') ? `Capture it from a ${prompt.camera_position}. ` : ''}` +
    `${(prompt.camera_lenses !== '') ? `Use ${prompt.camera_lenses}. ` : ''}` +
    `${(prompt.camera_shot !== '') ? `The camera shot should be a ${prompt.camera_shot}. ` : ''}` +
    `${(prompt.lighting !== '') ? `Illuminate with ${prompt.lighting}. ` : ''}` +
    `${(prompt.film_type !== '') ? `Consider using ${prompt.film_type} film for added effect.` : ''}`
}
