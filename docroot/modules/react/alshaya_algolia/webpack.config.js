var path  = require("path");

var config = {
  entry: {
    algolia: './js/algolia',
  },
  mode: 'production',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].js',
    publicPath: '/'
  },
  devServer: {
    contentBase: './',
    publicPath: '/'
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: ['babel-loader']
      }
    ]
  },
  externals : {
    'react': "React",
    'react-dom': "ReactDOM",
  },
  optimization: {
    splitChunks: {
      name: true,
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/](algoliasearch|react-instantsearch-dom)[\\/]/,
          name: 'algolia.bundle',
          chunks: 'all',
        }
      }
    }
  }
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'source-map';
  }
  return config;
};
