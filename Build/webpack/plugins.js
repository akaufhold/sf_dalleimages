/* global
    require, module
*/
const StylelintBarePlugin = require("stylelint-bare-webpack-plugin");
const StyleLintPlugin = new StylelintBarePlugin({
  configFile: "stylelint.config.cjs",
  failOnError: true,
  emitErrors: true,
  fix: true
});

module.exports = {
  StyleLintPlugin: StyleLintPlugin,
};