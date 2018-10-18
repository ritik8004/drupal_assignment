/**
 * @file
 * Task: Build.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('build', [
    'compile:sass',
  ], function (cb) {
  // Run linting last, otherwise its output gets lost.
    plugins.runSequence(['lint:css-with-fail'], cb);
  });

  gulp.task('build:dev', [
    'compile:sass',
  ], function (cb) {
    cd;
    // Run linting last, otherwise its output gets lost.
    plugins.runSequence(['lint:css'], cb);
  });
};
