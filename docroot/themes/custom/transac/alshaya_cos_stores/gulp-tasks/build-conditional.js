/**
 * @file
 * Task: Build.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('build:conditional', [
    'compile:module-component-libraries-rtl',
    'compile:module-component-libraries-ltr'
  ], function (cb) {
  // Run linting last, otherwise its output gets lost.
    plugins.runSequence(['lint:js-with-fail', 'lint:css-with-fail', 'lint:module-component-libraries-css-with-fail'], cb);
  });
};
