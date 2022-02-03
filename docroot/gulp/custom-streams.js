/**
 * @file
 * Custom streams.
 */

"use strict";

module.exports = function (libraries, { babelOptions, excludeStrict }) {
  const { lazypipe, babel, gulpIf, iife, through } = libraries;

  // Babel build streams on gulp-if.
  const babelBuild = lazypipe().pipe(babel, babelOptions);

  // Use this iifeBuild if 'excludeStrict' is not empty.
  const iifeBuild = lazypipe().pipe(() => {
    return gulpIf(excludeStrict, iife({ useStrict: false }), iife());
  });

  // Custom stream to fetch file relative paths.
  const relativePaths = (pathList = []) => {
    return through.obj((file, encoding, callback) => {
      pathList.push(file.relative);
      callback();
    });
  };

  return {
    babelBuild,
    iifeBuild,
    relativePaths,
  };
};
