var path = require("path");
const buildPath = './dist/';

module.exports = {
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
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /(node_modules|bower_components)/,
        use: ['babel-loader']
      }
    ]
  },
};
