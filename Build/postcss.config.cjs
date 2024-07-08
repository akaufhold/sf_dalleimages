module.exports = {
  plugins: {
    // inline-svg
    'postcss-inline-svg': {},

    // svgo
    'postcss-svgo': {},

    // preset-env
    'postcss-preset-env': {
      browsers: 'defaults'
    },

    // pxtorem
    'postcss-pxtorem': {
      rootValue: 16,
      propList: ['*']
    }
  }
}
