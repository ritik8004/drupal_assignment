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
    plpv2: './js/src/PlpV2Index.js',
    productCategoryCarousel: './js/src/ProductCategoryCarousel.js',
    ProductCategoryCarouselRcs: './js/src/ProductCategoryCarouselRcs.js',
  },
  mode: 'production',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].js',
    publicPath: buildPath,
    chunkFilename: "[name]-[chunkhash].js",
    jsonpFunction: "jsonpAlsAlg",
  },
  devServer: {
    contentBase: './',
    publicPath: '/dist/'
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
        exclude: /node_modules/,
        loader: "babel-loader",
        options: {
          rootMode: "upward"
        }
      },
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
