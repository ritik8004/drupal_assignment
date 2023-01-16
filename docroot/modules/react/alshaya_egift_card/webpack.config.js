var path = require("path");
var buildPath = '/modules/react/alshaya_egift_card/dist/';

var config = {
  entry: {
    my_egift_card: './js/my-egift-card',
    egift_card_purchase: './js/egift-card-purchase',
    egift_topup_purchase: './js/egift-topup-purchase',
    egift_check_balance: './js/egift-check-balance',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].bundle.js',
    publicPath: buildPath,
    chunkFilename: "[id].chunk.[chunkhash].js",
    jsonpFunction: "jsonpAlsEGC",
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
        loader: 'babel-loader',
        options: {
          presets: ['@babel/preset-env',
            '@babel/react',{
              'plugins': ['@babel/plugin-proposal-class-properties']}]
        }
      },
    ],
  },
  // Don't follow/bundle these modules, these are added in the *.libraries.yml.
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM'
  },
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
