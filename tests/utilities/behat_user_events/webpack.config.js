var path = require("path");
const buildPath = '../../../docroot/modules/custom/alshaya_behat/js/';

const config = {
  entry: {
    user_events: './js/user_events.js',
  },
  output: {
    path: path.resolve(buildPath),
    filename: '[name].bundle.js',
    publicPath: buildPath
  },
  devServer: {
    contentBase: './',
    publicPath: buildPath
  }
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'source-map';
    config.externals = {};
  }
  return config;
};
