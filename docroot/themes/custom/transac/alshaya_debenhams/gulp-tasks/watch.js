/**
 * @file
 * Task: Watch.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('watch', ['watch:sass', 'watch:js', 'watch:module-component-libraries']);

  gulp.task('watch:js', function () {
    return gulp.watch([
      options.js.files
    ], ['lint:js', 'lint:css']);
  });

  gulp.task('watch:sass', function () {
    return gulp.watch([
      options.sass.files
    ], ['compile:sass-dev', 'minify:css']);
  });

  gulp.task('watch:module-component-libraries', function () {
    return gulp.watch([
      options.sass.directionalSource
    ], ['compile:sass-dev', 'compile:module-component-libraries-rtl', 'compile:module-component-libraries-ltr', 'minify:css']);
  });
};
