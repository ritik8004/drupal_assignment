var path = require("path");
var buildPath = '/modules/react/alshaya_bazaar_voice/dist/';

var config = {
  entry: {
    rating: './js/src/rating',
    reviews: './js/src/reviews',
    myaccount: './js/src/myaccount',
    myorders: './js/src/myorders',
    reviewsV2: './js/src/reviewsV2',
    ratingV2: './js/src/ratingV2',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].bundle.js',
    publicPath: buildPath,
    chunkFilename: "[id].chunk.[chunkhash].js",
    jsonpFunction: "jsonpAlsBv",
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
