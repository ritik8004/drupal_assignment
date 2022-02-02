/**
 * @file
 * Task: JS Performance.
 */

"use strict";

module.exports = function (libraries, config, streams) {
  const { argv, src, ignore, gulpIf, iife, uglify, dest } = libraries;
  const { blackList, babelBuildPaths, iifeFiles, buildPath } = config;
  const { babelBuild, iifeBuild } = streams;

  // JS Performance task.
  const js_performance = () => {
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

    return (
      src(filterPath, { base: ".", allowEmpty: true })
        .pipe(ignore.exclude(blackList))
        .pipe(gulpIf(babelBuildPaths, babelBuild()))
        .pipe(gulpIf(iifeFiles, iife())) // if 'config.excludeStrict' is empty.
        // .pipe(gulpIf(iifeFiles, iifeBuild())) // if 'config.excludeStrict' is not empty.
        .pipe(uglify())
        .pipe(dest(buildPath.base))
    );
  };

  return js_performance;
};
