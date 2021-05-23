var path  = require("path");
var buildPath = '/modules/react/alshaya_algolia_react/dist/';
// This plugin is used to rename chunks.
// We use it currently to rename the Add to Bag js chunk to prevent it from
// getting cached by CDNs.
const ChunkRenamePlugin = require('webpack-chunk-rename-plugin');

var config = {
  entry: {
    autocomplete: './js/src/SearchIndex.js',
    plp: './js/src/PlpIndex.js',
  },
  mode: 'production',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].js',
    publicPath: buildPath,
    chunkFilename: "[name]-[chunkhash].js",
  },
  devServer: {
    contentBase: './',
    publicPath: '/dist/'
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
        loader: "babel-loader",
        options: {
          rootMode: "upward"
        }
      },
      {
        test: /\.css$/,
        loader: "style-loader!css-loader"
      },
      {
        test: /\.(woff|woff2|ttf|otf|eot|svg|gif)$/,
        use: [ 'file-loader' ],
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
  },
  plugins: [
    new ChunkRenamePlugin({
      // Add to bag js chunk will now contain chunk hash.
      'atb': '[name]-[chunkHash].js',
      // Algolia bundle chunk will remain as is, i.e. without any chunk hash.
      'algolia.bundle': 'algolia.bundle.js',
    }),
  ],
};

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config.devtool = 'source-map';
    config.externals = {};
  }
  return config;
};
