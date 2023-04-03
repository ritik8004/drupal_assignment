var path = require("path");
var buildPath = '/modules/react/alshaya_spc/dist/';

var config = {
  entry: {
    cart: './js/cart',
    minicart: './js/minicart',
    expressdelivery: './js/expressdelivery',
    cart_notification_drawer: './js/cart_notification_drawer',
    checkout: './js/checkout',
    order_details: './js/order-details',
    checkout_confirmation: './js/checkout-confirmation',
    backend_cart: './js/backend/v2/cart.js',
    backend_checkout: './js/backend/v2/checkout.js',
    dynamic_promotion_label: './js/promotions-dynamic-labels.js',
    // This is dynamically added in alshaya_rcs_product_library_info_alter().
    PdpRcsExpressDelivery: './js/PdpRcsExpressDelivery',
    pdp_sdd_ed_labels: './js/pdp_sdd_ed_labels.js',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].bundle.js',
    publicPath: buildPath,
    chunkFilename: "[id].chunk.[chunkhash].js",
    jsonpFunction: "jsonpAlsSpc",
  },
  devServer: {
    contentBase: './',
    publicPath: buildPath
  },
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
    axios: 'axios',
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /(node_modules|bower_components)/,
        loader: 'babel-loader',
        options: {
          rootMode: "upward",
          presets: ['@babel/preset-env',
            '@babel/react',{
              'plugins': ['@babel/plugin-proposal-class-properties']}]
        }
      },
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
