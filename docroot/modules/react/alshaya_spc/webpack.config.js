var path = require("path");
var buildPath = '/modules/react/alshaya_spc/dist/';

var config = {
  entry: {
    cart: './js/cart',
    minicart: './js/minicart',
    checkout: './js/checkout',
    checkout_confirmation: './js/checkout-confirmation'
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
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM'
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /(node_modules|bower_components)/,
        use: ['babel-loader']
      }
    ]
  }
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'source-map';
    config.externals = {};
  }

  return config;
}
