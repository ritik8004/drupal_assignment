/**
 * @file
 * Gulp file to call all the gulp related task.
 *
 * Usage:
 * To build all JS files - `npm run build`
 * To build specific directory - `npm run build -- --path=themes/custom/transac/alshaya_white_label`
 * To build specific file - `npm run build -- --path=themes/custom/transac/alshaya_white_label/js/component.js`
 */

// Required libraries and plugins.
const fs = require("fs");
const del = require("del");
const through = require("through2");
const lazypipe = require("lazypipe");
const gulpPlugins = require("gulp-load-plugins")();
const gulpIf = gulpPlugins.if;
const { ignore, uglify, babel, iife } = gulpPlugins;
const { src, dest, series } = require("gulp");
const { argv } = require("yargs");

// Libraries grouped to a single object for passing as parameter.
const libraries = {
  argv,
  babel,
  del,
  dest,
  fs,
  gulpIf,
  ignore,
  iife,
  lazypipe,
  series,
  src,
  through,
  uglify,
};

// Configuration Settings.
const config = require("./gulp/config");

// Custom helper streams.
const customStreams = require("./gulp/custom-streams")(libraries, config);

// Clean Task.
const clean = require("./gulp/clean")(libraries, config);

// JS Performance Task.
const js_performance = require("./gulp/js-performance")(
  libraries,
  config,
  customStreams
);

// Build path to JSON tasks.
const { extractBuildPaths, outputBuildPaths } = require("./gulp/build-paths")(
  libraries,
  config,
  customStreams
);

// JS Performance Build Task.
exports["performance-build"] = series(
  clean,
  js_performance,
  extractBuildPaths,
  outputBuildPaths
);
