/**
 * @file
 * Task: Build.
 */

module.exports = function (gulp) {
  'use strict';

  gulp.task('build', gulp.parallel(
    'compile:sass',
    'compile:module-component-libraries-rtl',
    'compile:module-component-libraries-ltr'
  ));

  gulp.task('build:dev', gulp.parallel(
    'compile:sass-dev',
    'compile:module-component-libraries-rtl-dev',
    'compile:module-component-libraries-ltr-dev'
  ));

  gulp.task('lint', gulp.parallel(
    'lint:js-with-fail',
    'lint:css-with-fail',
    'lint:module-component-libraries-css-with-fail'
  ));
};
