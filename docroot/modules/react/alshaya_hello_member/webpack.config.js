var path = require("path");
var buildPath = '/modules/react/alshaya_hello_member/dist/';

var config = {
  entry: {
    my_accounts: './js/src/my-accounts.js',
    my_membership_info: './js/src/my-membership-info.js',
    hello_member_benefits_page: './js/src/hello-member-benefits-page.js',
    my_accounts_points_history: './js/src/my-accounts-points-history.js',
    pdp: './js/src/pdp.js',
    send_otp: './js/src/send-otp.js',
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
        loader: 'babel-loader',
        options: {
          rootMode: "upward",
          presets: ['@babel/preset-env',
            '@babel/react',{
              'plugins': ['@babel/plugin-proposal-class-properties']}]
        }
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
