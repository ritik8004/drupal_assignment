var path = require("path");
var buildPath = '/modules/react/alshaya_matchback/dist/';
var config = {
  entry: {
    matchback_addtobag: './js/matchbackAddToBag',
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
    'react-dom': 'ReactDOM',
    axios: 'axios',
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
