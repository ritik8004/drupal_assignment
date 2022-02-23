var path = require("path");
var buildPath = '/modules/react/alshaya_appointment/dist/';

var config = {
  entry: {
    appointment: './js/appointment',
    appointments_view: './js/appointments.view',
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].js',
    publicPath: buildPath,
    chunkFilename: "[id].chunk.[chunkhash].js",
    jsonpFunction: "jsonpAlsAppmnt",
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
        loader: 'babel-loader',
        options: {
          presets: ['@babel/preset-env',
            '@babel/react',{
              'plugins': ['@babel/plugin-proposal-class-properties']}]
        }
      },
      {
        test: /\.css$/,
        use: [ 'style-loader', 'css-loader' ],
      },
      {
        test: /\.(woff|woff2|ttf|otf|eot|svg|gif)$/,
        use: [ 'file-loader' ],
      },
    ],
  },
  // Don't follow/bundle these modules, these are added in the *.libraries.yml.
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
    axios: 'axios',
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
