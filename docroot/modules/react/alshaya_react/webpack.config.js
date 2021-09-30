const path = require('path');

const config = {
  entry: {
    number: ['core-js/modules/es.number.is-integer', 'core-js/modules/es.number.is-nan'],
    promise: ['core-js/modules/es.promise'],
    object: [
      'core-js/modules/es.object.entries',
      'core-js/modules/es.object.keys',
      'core-js/modules/es.object.values',
      'core-js/modules/es.object.assign',
      'core-js/modules/es.array.for-each',
      'core-js/modules/es.array.iterator',
    ],
  },
  mode: 'production',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].bundle.js',
    publicPath: '/',
  },
  devServer: {
    contentBase: './',
    publicPath: '/',
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
        use: ['babel-loader'],
      },
    ],
  },
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'source-map';
    config.externals = {};
  }
  return config;
};
