/**
 * @file
 * Task: Build.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('build', [
    'compile:sass',
  ]);

  gulp.task('build:dev', [
    'compile:sass',
  ]);

  gulp.task('lint', [
    'lint:js-with-fail',
    'lint:css-with-fail'
  ]);
};
