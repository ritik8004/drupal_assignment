var path = require("path");
var buildPath = '/modules/react/alshaya_spc/dist/';

var config = {
  entry: {
    number: ["core-js/modules/es.number.is-integer", "core-js/modules/es.number.is-nan"],
    promise: ["core-js/modules/es.promise", "core-js/modules/es.array.iterator"],
    object: [
      "core-js/modules/es.object.entries",
      "core-js/modules/es.object.keys",
      "core-js/modules/es.object.values",
      "core-js/modules/es.object.assign",
      "core-js/modules/es.array.for-each",
    ],
    cart: './js/cart',
    minicart: './js/minicart',
    checkout: './js/checkout',
    checkout_confirmation: './js/checkout-confirmation',
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

  config.watchOptions = {
    poll: 800,
    ignored: /node_modules/
  };

  return config;
};
