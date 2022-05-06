/**
 * @file
 * Task: JS Performance.
 */

"use strict";

const jsPerformanceTask = (filterPath, isDev, libraries, config, streams) => {
  const { src, ignore, gulpIf, iife, uglify, dest, sourcemaps } = libraries;
  const { blackList, babelBuildPaths, buildPath, iifeFiles, uglifyOptions } = config;
  const { babelBuild, iifeBuild } = streams;

  let task = src(filterPath, { base: ".", allowEmpty: true }).pipe(
    ignore.exclude(blackList)
  );

  if (isDev) {
    task = task.pipe(sourcemaps.init());
  }

  task = task
    .pipe(gulpIf(babelBuildPaths, babelBuild()))
    .pipe(gulpIf(iifeFiles, iife())) // if 'config.excludeStrict' is empty.
    // .pipe(gulpIf(iifeFiles, iifeBuild())) // if 'config.excludeStrict' is not empty.
    .pipe(uglify(uglifyOptions));

  if (isDev) {
    task = task.pipe(sourcemaps.write());
  }

  return task.pipe(dest(buildPath.base));
};

const getJsFilterPath = ({ argv }) => {
  let filterPath = "./**/*.js";
  if (argv.path) {
    // Search directory passed via arguments.
    filterPath = argv.path;
    if (filterPath.startsWith("/")) {
      filterPath = filterPath.substring(1);
    }
    if (!filterPath.endsWith(".js")) {
      if (!filterPath.endsWith("/")) {
        filterPath = filterPath + "/";
      }
      filterPath = filterPath + "**/*.js";
    }
  }
  return filterPath;
};

module.exports = function (libraries, config, streams) {
  const filterPath = getJsFilterPath(libraries);

  // JS Performance Gulp tasks.
  const jsPerformance = () =>
    jsPerformanceTask(filterPath, false, libraries, config, streams);
  const jsPerformanceDev = () =>
    jsPerformanceTask(filterPath, true, libraries, config, streams);

  return { jsPerformance, jsPerformanceDev };
};
