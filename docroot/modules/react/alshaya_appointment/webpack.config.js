var path  = require("path");
var buildPath = '/modules/react/alshaya_appointment/dist/';

module.exports = {
  entry: {
    appointment: './js/appointment',
    appointments_view: './js/appointments.view',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].js',
    publicPath: buildPath,
    chunkFilename: "[id].chunk.[chunkhash].js",
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
        use: ['babel-loader']
      }
    ]
  },
  // Don't follow/bundle these modules, these are added in the *.libraries.yml.
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM'
  },
};
