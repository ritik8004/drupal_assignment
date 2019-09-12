var path  = require("path");

module.exports = {
  entry: {
    test: './js/test',
    custom: './js/custom',
  },
  output: {
    path: path.resolve(__dirname, 'js/dist'),
    filename: '[name].js',
    publicPath: '/'
  },
  devServer: {
    contentBase: './',
    publicPath: '/dist/'
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
