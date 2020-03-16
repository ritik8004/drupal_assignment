// We are using airbnb plugin and in that plugin eslint-plugin-jsx-a11y
// is required dependency and we are not following it. we can not remove
// it from dependency. hence, have to disable all rules dynamically here.
const a11yOff = Object
  .keys(require('eslint-plugin-jsx-a11y').rules)
  .reduce(
    (acc, rule) => {
      acc[`jsx-a11y/${rule}`] = 'off';
      return acc;
    }, {}
  );

module.exports = {
  root: true,
  env: {
    browser: true,
    es6: true,
    node: true,
  },
  extends: [
    'plugin:react/recommended',
    'airbnb',
  ],
  globals: {
    Drupal: true,
    drupalSettings: true,
    drupalTranslations: true,
    domready: true,
    jQuery: true,
    google: true,
  },
  parser: "babel-eslint",
  parserOptions: {
    ecmaFeatures: {
      jsx: true,
    },
    ecmaVersion: 2018,
    sourceType: 'module',
  },
  plugins: [
    'react',
  ],
  rules: {
    ...a11yOff,
    "react/jsx-filename-extension": [1, { "extensions": [".js", ".jsx"] }],
    "import/no-extraneous-dependencies": "off",
    "react/prop-types": [0],
    "no-plusplus": ["error", { "allowForLoopAfterthoughts": true }],
    "react/static-property-placement": ["warn", "property assignment", {
      contextType: "static public field",
    }],
    "react/sort-comp": [1, {
      order: [
        'propTypes',
        'instance-variables',
        'static-variables',
        'static-methods',
        'lifecycle',
        'everything-else',
        'render'
      ]
    }],
  }
};
