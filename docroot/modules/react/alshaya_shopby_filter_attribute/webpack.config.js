var path = require("path");
var buildPath = '/modules/react/alshaya_main_menu_attribute_navigation/dist/';
var config = {
  entry: {
    menu_attribute_navigation: './js/menu_attribute_navigation.js',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].bundle.js',
    publicPath: buildPath,
    chunkFilename: "[id].chunk.[chunkhash].js",
    jsonpFunction: "jsonpAlsSS",
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
    ],
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
