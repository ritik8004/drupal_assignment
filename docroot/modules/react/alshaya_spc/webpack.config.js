var path = require("path");
var buildPath = '/modules/react/alshaya_spc/dist/';

var config = {
  entry: {
    cart: './js/cart',
    minicart: './js/minicart',
    checkout: './js/checkout',
    checkout_confirmation: './js/checkout-confirmation',
    backend_cart_v1: './js/backend/v1/cart.js',
    backend_cart_v2: './js/backend/v2/cart.js',
    backend_checkout_v1: './js/backend/v1/checkout.js',
    backend_checkout_v2: './js/backend/v2/checkout.js',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].bundle.js',
    publicPath: buildPath,
    chunkFilename: "[id].chunk.[chunkhash].js",
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
        loader: "babel-loader",
        options: {
          rootMode: "upward",
        }
      }
    ]
  }
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'source-map';
    config.externals = {};
  }

  config.watchOptions = {
    poll: 800,
    ignored: /node_modules/
  };

  return config;
};
