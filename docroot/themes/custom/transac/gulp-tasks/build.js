/**
 * @file
 * Task: Build.
 */

module.exports = function (gulp) {
  'use strict';

  gulp.task('build', [
    'compile:sass',
    'compile:module-component-libraries-rtl',
    'compile:module-component-libraries-ltr'
  ]);

  gulp.task('lint', [
    'lint:js-with-fail',
    'lint:css-with-fail',
    'lint:module-component-libraries-css-with-fail'
  ]);
};
