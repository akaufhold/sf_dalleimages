const webpack = require('webpack')
const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const StyleLintPlugin = require('stylelint-webpack-plugin')
const ESLintPlugin = require('eslint-webpack-plugin')
const CopyPlugin = require('copy-webpack-plugin')

module.exports = {
  // Define the entry points of our application (can be multiple for different sections of a website)
  entry: {
    Backend: './Assets/Scripts/backend.js',
    sizeOptions: './Assets/Scripts/sizeOptions.js',
  },

  // Define the destination directory and filenames of compiled resources and files
  output: {
    filename: 'JavaScripts/[name].js',
    path: path.resolve(__dirname, '../Resources/Public'),
    assetModuleFilename: '[name][ext]',
    clean: true
  },

  // Other webpack configuration...
  resolve: {
    alias: {
        '@typo3/core': path.resolve(__dirname, '../../../vendor/typo3/cms-core/Resources/Public/JavaScript')
    }
  },

  // Define loaders
  module: {
    rules: [
      // CSS, PostCSS, and Sass
      {
        test: /\.(scss|css)$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              importLoaders: 2,
              sourceMap: false,
              url: true
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: [
                  'autoprefixer'
                ]
              }
            }
          },
          'sass-loader'
        ]
      },
      {
        test: [/\.bmp$/, /\.gif$/, /\.jpe?g$/, /\.png$/],
        type: 'asset/resource',
        generator: {
          filename: 'Images/[name][ext]'
        }
      },
      {
        test: [/\.woff$/, /\.woff2$/],
        type: 'asset/resource',
        generator: {
          filename: 'Fonts/[name][ext]'
        }
      }
    ]
  },

  // Define used plugins
  plugins: [
    new ESLintPlugin({
      failOnError: true
    }),

    new StyleLintPlugin({
      configFile: 'stylelint.config.js',
      context: 'Assets',
      files: '**/*.s?(a|c)ss',
      failOnError: true,
      emitErrors: true,
      fix: true
    }),

    new CopyPlugin({
      patterns: [
        {from: './Assets/Static', to: './'}
      ]
    }),

    // Extracts CSS into separate files
    new MiniCssExtractPlugin({
      filename: 'StyleSheets/[name].css',
      chunkFilename: '[id].css'
    })
  ]
}
