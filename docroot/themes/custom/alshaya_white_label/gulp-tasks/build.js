/**
 * @file
 * Task: Build.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('build', [
    'compile:sass',
    // 'compile:styleguide'
  ], function (cb) {
  // Run linting last, otherwise its output gets lost.
    plugins.runSequence(['lint:js-with-fail', 'lint:css-with-fail'], cb);
  });

  gulp.task('build:dev', [
    'compile:sass',
    // 'compile:styleguide'
  ], function (cb) {
    // Run linting last, otherwise its output gets lost.
    plugins.runSequence(['lint:js', 'lint:css'], cb);
  });
};
