const gulp = require('gulp');
const webpack = require('webpack-stream');

gulp.task('default', function () {
  return gulp.src('js/*.js')
    .pipe(webpack( require('./webpack.config.js') ))
    .pipe(gulp.dest('dist/'));
});

gulp.task('watch', function () {
  return gulp.src('js/*.js')
    .pipe(webpack({
      config : require('./webpack.config.js'),
      watch: true,
    }))
    .pipe(gulp.dest('dist/'));
});
