import {capitalize, indefiniteArticle} from './utilities'

/* Generate text prompt for dalle image api call */
export const finalPrompt = (prompt) => {
  const article = indefiniteArticle(prompt.colors + prompt.subject)
  return `${(prompt.illustration !== '') ? `${capitalize(indefiniteArticle(prompt.illustration))} ${prompt.illustration} of ${article} ` : `${article} `}` +
  `${(prompt.colors) ? `${prompt.colors} ` : ''}` +
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
