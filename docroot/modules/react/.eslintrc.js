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
  },
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
    "react/jsx-filename-extension": [1, {"extensions": [".js", ".jsx"]}],
    "import/no-extraneous-dependencies": "off",
    "react/prop-types": [0],
  }
};
