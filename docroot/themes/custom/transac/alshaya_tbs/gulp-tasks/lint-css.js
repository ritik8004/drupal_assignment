/**
 * @file
 * Task: Lint: scss Files.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  // Lint scss files.
  gulp.task('lint:css', function () {
    return gulp.src(options.sass.files)
      .pipe(plugins.plumber())
      .pipe(plugins.gulpStylelint({
        reporters: [
            {
                formatter: 'string',
                console: true
            }
        ]
      }))
      .pipe(plugins.plumber.stop());
  });

  // Lint scss files and throw an error for a CI to catch.
  gulp.task('lint:css-with-fail', function () {
    return gulp.src(options.sass.files)
      .pipe(plugins.gulpStylelint({
        reporters: [
            {
                formatter: 'string',
                console: true,
                failAfterError: true
            }
        ]
      }))
  });

  // Lint module-component-libraries scss files.
  gulp.task('lint:module-component-libraries-css', function () {
    return gulp.src(options.sass.directionalSource)
      .pipe(plugins.plumber())
      .pipe(plugins.gulpStylelint({
        reporters: [
          {
            formatter: 'string',
            console: true
          }
        ]
      }))
      .pipe(plugins.plumber.stop());
  });

  // Lint module-component-libraries scss files and throw an error for a CI to catch.
  gulp.task('lint:module-component-libraries-css-with-fail', function () {
    return gulp.src(options.sass.directionalSource)
      .pipe(plugins.gulpStylelint({
        reporters: [
          {
            formatter: 'string',
            console: true,
            failAfterError: true
          }
        ]
      }))
  });
};
