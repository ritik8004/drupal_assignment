# Alshaya React

This module provides basic library required for react to run with alshaya
setup, and it contains an example module to help you build custom react
modules.

# Prerequisite :bell:
- You have already setup vagrant
- `vagrant ssh`
- From `docroot/modules/react` directory, Execute `npm install` / `yarn install`. :see_no_evil:

# Usage :hammer_and_wrench:
- Example module created: `./alshaya_react/modules/alshaya_react_test` :file_folder:
- Copy files in your custom module to use the existing packages: :clipboard:
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
- Transpile your module's react/js files. :gear:
    > :sparkles: Execute these commands from your module directory only.
    - `npm run build` :speedboat:
    - `npm run watch` :rowboat:
