var path = require("path");
var buildPath = '/modules/react/alshaya_geolocation/dist/';

var config = {
  entry: {
    store_finder: './js/src/store-finder.js',
    store_finder_list: './js/src/store-finder-list.js',
    store_click_collect_list: './js/src/store-click-collect-list.js',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].bundle.js',
    publicPath: buildPath,
    chunkFilename: '[id].chunk.[chunkhash].js',
    jsonpFunction: 'jsonpAlsGl',
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
