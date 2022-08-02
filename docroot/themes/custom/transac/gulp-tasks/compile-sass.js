/**
 * @file
 * Task: Compile: Sass.
 */

const gulp = require("gulp");
module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('compile:sass', function () {
    return gulp.src([options.sass.files])
      .pipe(plugins.sourcemaps.init())
      .pipe(plugins.sassGlob())
      .pipe(plugins.sass({
        errLogToConsole: true,
        outputStyle: 'compressed',
      }))
      .pipe(plugins.postcss([plugins.autoprefixer({
        grid: true,
        cascade: false
      })]))
      .pipe(plugins.cleanCss({
        level: 2
      }))
      .pipe(plugins.sourcemaps.write())
      .pipe(gulp.dest(options.sass.destination));
  });

  gulp.task('compile:sass-dev', function () {
    return gulp.src([options.sass.files])
      .pipe(plugins.sourcemaps.init())
      .pipe(plugins.sassGlob())
      .pipe(plugins.sass({
        errLogToConsole: true,
        outputStyle: 'expanded'
      }))
      .pipe(plugins.postcss([plugins.autoprefixer({
        grid: true,
        cascade: false
      })]))
      .pipe(plugins.cleanCss({
        level: 2
      }))
      .pipe(plugins.sourcemaps.write())
      .pipe(gulp.dest(options.sass.destination));
  });

  gulp.task('compile:module-component-libraries-ltr', function () {
    return gulp.src([options.sass.directionalSource])
    .pipe(plugins.sourcemaps.init())
    .pipe(plugins.sassGlob())
    .pipe(plugins.sass({
      errLogToConsole: true,
      outputStyle: 'compressed'
    }))
    .pipe(plugins.postcss([plugins.autoprefixer({
      grid: true,
      cascade: false
    })]))
    .pipe(plugins.cleanCss({
      level: 2
    }))
    .pipe(plugins.rename({
      suffix: '.ltr'
    }))
    .pipe(plugins.sourcemaps.write())
    .pipe(gulp.dest(options.sass.directionalDestination));
  });

  gulp.task('compile:module-component-libraries-ltr-dev', function () {
    return gulp.src([options.sass.directionalSource])
      .pipe(plugins.sourcemaps.init())
      .pipe(plugins.sassGlob())
      .pipe(plugins.sass({
        errLogToConsole: true,
        outputStyle: 'expanded'
      }))
      .pipe(plugins.postcss([plugins.autoprefixer({
        grid: true,
        cascade: false
      })]))
      .pipe(plugins.cleanCss({
        level: 2
      }))
      .pipe(plugins.rename({
        suffix: '.ltr'
      }))
      .pipe(plugins.sourcemaps.write())
      .pipe(gulp.dest(options.sass.directionalDestination));
  });

  gulp.task('compile:module-component-libraries-rtl', function () {
    return gulp.src([options.sass.directionalSource])
    .pipe(plugins.sourcemaps.init())
    .pipe(plugins.sassGlob())
    .pipe(plugins.sassVariables(options.sass.variables))
    .pipe(plugins.sass({
      errLogToConsole: true,
      outputStyle: 'compressed'
    }))
    .pipe(plugins.postcss([plugins.autoprefixer({
      grid: true,
      cascade: false
    })]))
    .pipe(plugins.cleanCss({
      level: 2
    }))
    .pipe(plugins.rename({
      suffix: '.rtl'
    }))
    .pipe(plugins.sourcemaps.write())
    .pipe(gulp.dest(options.sass.directionalDestination));
  });

  gulp.task('compile:module-component-libraries-rtl-dev', function () {
    return gulp.src([options.sass.directionalSource])
      .pipe(plugins.sourcemaps.init())
      .pipe(plugins.sassGlob())
      .pipe(plugins.sassVariables(options.sass.variables))
      .pipe(plugins.sass({
        errLogToConsole: true,
        outputStyle: 'expanded'
      }))
      .pipe(plugins.postcss([plugins.autoprefixer({
        grid: true,
        cascade: false
      })]))
      .pipe(plugins.cleanCss({
        level: 2
      }))
      .pipe(plugins.rename({
        suffix: '.rtl'
      }))
      .pipe(plugins.sourcemaps.write())
      .pipe(gulp.dest(options.sass.directionalDestination));
  });
};
