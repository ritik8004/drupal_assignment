"use strict";

// Exclude from JS Gulp build.
const blackList = [
  // libraries and already built files.
  "**/dist/**",
  "**/node_modules/**",
  "**/vendor/**",
  "**/test/**",
  "**/tests/**",
  "libraries/**",
  "sites/**",
  "build/**",
  "**/*.min.js",
  "**/*-min.js",
  "**/*.bundle.js",
  "**/webpack.config.js",
  "**/gulp*/**",
  "**/gulpfile.js",
  "**/*worker.js",

  // React codes are already built and minified.
  "modules/react/alshaya_add_to_bag/**",
  "modules/react/alshaya_algolia_react/js/algolia/**",
  "modules/react/alshaya_algolia_react/js/common/**",
  "modules/react/alshaya_algolia_react/js/src/**",
  "modules/react/alshaya_appointment/js/**",
  "modules/react/alshaya_aura_react/js/**",
  "modules/react/alshaya_bazaar_voice/js/src/**",
  "modules/react/alshaya_fit_calculator/**",
  "modules/react/alshaya_pdp_react/**",
  "modules/react/alshaya_react/**",
  "modules/react/alshaya_sofa_sectional/js/**",
  "modules/react/alshaya_spc/js/**",
  "modules/react/alshaya_stylefinder/**",
  "modules/react/alshaya_wishlist/js/**",
  "modules/react/js/**",
  "modules/contrib/lightning_workflow/modules/lightning_scheduler/**",

  // Other problematic or empty files.
  "themes/contrib/kashmir/components/_annotations/annotations.js",
  "themes/contrib/kashmir/js/global.js",
  "themes/custom/non_transac/whitelabel/components/_annotations/annotations.js",
  "modules/contrib/webform/js/webform.assets.js",
];

// Exclude from adding 'use strict'.
const excludeStrict = [];

const iifeFiles = [
  // ALL of previously filtered paths.
  "**/*",
  // NOT of following files.
  "!themes/custom/non_transac/whitelabel/components/js/utils.js",
  "!themes/custom/transac/alshaya_white_label/js/utils.js",
];

// Babel build only on custom JS.
const babelBuildPaths = [
  "modules/**",
  "!modules/contrib/**",
  "themes/**",
  "!themes/contrib/**",
];

// Babel configuration settings.
const babelOptions = {
  configFile: false,
  babelrc: false,
  presets: [
    ["@babel/preset-env", { targets: "defaults, not IE 11" }],
    "@babel/preset-react",
  ],
  plugins: [
    "transform-class-properties",
    "@babel/transform-react-constant-elements",
    "@babel/transform-react-inline-elements",
    [
      "@babel/plugin-proposal-decorators",
      {
        legacy: true,
      },
    ],
    [
      "@babel/plugin-transform-modules-commonjs",
      {
        strictMode: false,
      },
    ],
  ],
};

// Uglify JS settings.
const uglifyOptions = {
  webkit: true
};

// Final build path settings.
const _buildBase = "./build";
const buildPath = {
  base: _buildBase,
  resultJson: _buildBase + "/js-performance-build.json",
  scripts: _buildBase + "/**/*.js",
};

module.exports = {
  blackList,
  excludeStrict,
  iifeFiles,
  babelBuildPaths,
  babelOptions,
  buildPath,
  uglifyOptions,
};
