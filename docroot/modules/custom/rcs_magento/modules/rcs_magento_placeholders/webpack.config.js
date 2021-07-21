var path = require("path");
const buildPath = './dist/';

const config = {
  entry: {
    rcs_magento_placeholders: './js/rcs_magento_placeholders.js',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].bundle.js',
    publicPath: buildPath
  },
  devServer: {
    contentBase: './',
    publicPath: buildPath
  },
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'source-map';
    config.externals = {};
  }
  return config;
};
