var path = require("path");
const buildPath = './dist/';

const config = {
  entry: {
    main: './js/alshaya_rcs.es5.js',
    alshaya_rcs: './js/alshaya_rcs.js',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].js',
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
