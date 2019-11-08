var path  = require("path");

var config = {
  entry: {
    promise: ["core-js/modules/es.promise", "core-js/modules/es.array.iterator"],
    object: ["core-js/modules/es.object.entries", "core-js/modules/es.object.keys", "core-js/modules/es.object.values", "core-js/modules/es.array.for-each"],
    autocomplete: './js/src/',
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
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM'
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
  optimization: {
    splitChunks: {
      name: true,
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/](algoliasearch|react-instantsearch-dom)[\\/]/,
          name: 'algolia.bundle',
          chunks: 'all',
        },
      }
    }
  }
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'source-map';
    config.externals = {};
  }
  return config;
};
