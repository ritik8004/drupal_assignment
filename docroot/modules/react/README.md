# Alshaya React

This module provides basic library required for react to run with alshaya
setup, and it contains an example module to help you build custom react
modules.

# Prerequisites :bell:
- You have already setup vagrant
- `vagrant ssh`
- From `docroot/modules/react` directory, Execute `npm install` / `yarn install`. :see_no_evil:

# How to build a new react module? :hammer_and_wrench:
- Example module already exists at: `docroot/modules/react/alshaya_react/modules/alshaya_react_test` :file_folder:
- Copy files in your custom module from `docroot/modules/react/alshaya_react/modules/alshaya_react_test` to use the existing packages: :clipboard:
    -  `package.json`: Contains `scripts` to execute command from module directory.
    -  `.babelrc`: Contains babel related configs. we are using babel-loader with webapck.
    - `webpack.config.js`: We are using webpack to transpile js files
        - The Component and js files vary based on your module, hence you will
         have to update values for `entry` key.
        - For product purpose it would be nice to have single transpiled file, and
         to do that, For `entry` key; pass the array without any key like: 
        `['./js/test', './js/custom']` and that will create single `main.js` file.
    - (optional) `gulpfile.js`: :see_no_evil:
        > :exclamation: To transpile *.js files, you won't require gulpfile.js But in case if 
        you want to use gulpfile with webpack, the examples are added in the 
        file. (ref: https://www.npmjs.com/package/gulp-webpack)

# Transpile module's react/js files. :gear:
:sparkles: to make your module, compile js from module's directory only. for example to make algolia work on your local: 
- change directory to `docroot/modules/react/alshaya_algolia_react/` 
- `npm run build:dev` / `npm run watch` (For local development) :rowboat:
- `npm run build` (to check with production grade compiled files..) :speedboat: 
