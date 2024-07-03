export const capitalize = s => s && s[0].toUpperCase() + s.slice(1)

export const indefiniteArticle = (phrase) => {
  // Getting the first word
  const match = /\w+/.exec(phrase)
  if (match) {
    var word = match[0]
  } else {
    return 'an'
  }

  const lcword = word.toLowerCase()
  // Specific start of words that should be preceeded by 'an'
  const altCases = ['honest', 'hour', 'hono']
  for (const i in altCases) {
    if (lcword.indexOf(altCases[i]) === 0) {
      return 'an'
    }
  }

  // Single letter word which should be preceeded by 'an'
  if (lcword.length === 1) {
    if ('aedhilmnorsx'.indexOf(lcword) >= 0) {
      return 'an'
    } else {
      return 'a'
    }
  }

  // Capital words which should likely be preceeded by 'an'
  if (word.match(/(?!FJO|[HLMNS]Y.|RY[EO]|SQU|(F[LR]?|[HL]|MN?|N|RH?|S[CHKLMNPTVW]?|X(YL)?)[AEIOU])[FHLMNRSX][A-Z]/)) {
    return 'an'
  }

  // Special cases where a word that begins with a vowel should be preceeded by 'a'
  const regexes = [/^e[uw]/, /^onc?e\b/, /^uni([^nmd]|mo)/, /^u[bcfhjkqrst][aeiou]/]
  for (const j in regexes) {
    if (lcword.match(regexes[j])) {
      return 'a'
    }
  }

  // Special capital words (UK, UN)
  if (word.match(/^U[NK][AIEO]/)) {
    return 'a'
  } else if (word === word.toUpperCase()) {
    if ('aedhilmnorsx'.indexOf(lcword[0]) >= 0) {
      return 'an'
    } else {
      return 'a'
    }
  }

  // Basic method of words that begin with a vowel being preceeded by 'an'
  if ('aeiou'.indexOf(lcword[0]) >= 0) {
    return 'an'
  }

  // Instances where y follwed by specific letters is preceeded by 'an'
  if (lcword.match(/^y(b[lor]|cl[ea]|fere|gg|p[ios]|rou|tt)/)) {
    return 'an'
  }

  return 'a'
}
