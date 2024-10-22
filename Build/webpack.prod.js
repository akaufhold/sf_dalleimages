import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import StyleLintPlugin from 'stylelint-webpack-plugin';
import ESLintPlugin from 'eslint-webpack-plugin';
import CopyPlugin from 'copy-webpack-plugin';
import FixStyleOnlyEntriesPlugin from 'webpack-fix-style-only-entries';

export default {
  // Define the entry points of our application (can be multiple for different sections of a website)
  entry: {
    backend: './Assets/Scss/backend.scss',
    buttons: './Assets/Scripts/buttons.js',
    sizeOptions: './Assets/Scripts/sizeOptions.js',
  },

  // Define the destination directory and filenames of compiled resources and files
  output: {
    filename: 'JavaScripts/[name].js',
    path: path.resolve(__dirname, '../Resources/Public'),
    assetModuleFilename: '[name][ext]',
    clean: true,
  },

  // Other webpack configuration...
  resolve: {
    alias: {
      'TYPO3/CMS': path.resolve(__dirname, '../../../public/typo3/sysext/core/Resources/Public/JavaScript'),
      '@typo3/core': path.resolve(__dirname, '../../../vendor/typo3/cms-core/Resources/Public/JavaScript'),
    },
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
              url: true,
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: ['autoprefixer'],
              },
            },
          },
          'sass-loader',
        ],
      },
      {
        test: [/\.bmp$/, /\.gif$/, /\.jpe?g$/, /\.png$/],
        type: 'asset/resource',
        generator: {
          filename: 'Images/[name][ext]',
        },
      },
      {
        test: [/\.woff$/, /\.woff2$/],
        type: 'asset/resource',
        generator: {
          filename: 'Fonts/[name][ext]',
        },
      },
    ],
  },

  // Define used plugins
  plugins: [
    new ESLintPlugin({
      failOnError: true,
    }),

    new StyleLintPlugin({
      configFile: 'stylelint.config.cjs',
      context: 'Assets',
      files: '**/*.s?(a|c)ss',
      failOnError: true,
      emitErrors: true,
      fix: true,
    }),

    new CopyPlugin({
      patterns: [
        { from: './Assets/Static', to: './' },
      ],
    }),

    // Extracts CSS into separate files
    new MiniCssExtractPlugin({
      filename: 'StyleSheets/[name].css',
      chunkFilename: '[id].css',
    }),

    new FixStyleOnlyEntriesPlugin(),
  ],
};
