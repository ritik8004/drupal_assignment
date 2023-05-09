var path  = require("path");
var buildPath = '/modules/react/alshaya_aura_react/dist/';

var config = {
  entry: {
    loyalty_club: './js/my-loyalty-club',
    my_accounts: './js/my-accounts',
    header: './js/header',
    pdp: './js/pdp',
    // This is dynamically added in alshaya_rcs_product_library_info_alter().
    pdpRcs: './js/PdpRcs',
    aura_backend: './js/backend/v2/',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].bundle.js',
    publicPath: buildPath,
    chunkFilename: "[id].chunk.[chunkhash].js",
    jsonpFunction: "jsonpAlsAu",
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
