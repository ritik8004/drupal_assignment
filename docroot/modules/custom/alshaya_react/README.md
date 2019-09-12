# Alshaya React

This module provides basic library required for react to run with alshaya
setup, and it contains an example module to help you build custom react
modules.

# Prerequisite :bell:
- You have already setup vagrant
- `vagrant ssh`
- Execute `npm install` / `yarn install` on the alshaya root directory. :see_no_evil: 

# Usage :hammer_and_wrench:
- Example module created: `./modules/alshaya_react_test` :file_folder:
- Copy files in your custom module to use the existing packages: :clipboard:
    -  `package.json`: Contains `scripts` to execute command from module directory.
    -  `.babelrc`: Contains babel related configs. we are using babel-loader with webapck.
    - `webpack.config.js`: We are using webpack to transpile js files
    - `gulpfile.js`: (optional) :see_no_evil:
        > To transpile *.js files, you won't require gulpfile.js But in case if 
        you want to use gulpfile with webpack, the examples are added in the 
        file. (ref: https://www.npmjs.com/package/gulp-webpack)
- Transpile your module's react/js files. :gear:
    > Execute these commands from your module directory only.
    - `npm run build` :speedboat:
    - `npm run watch` :rowboat:
