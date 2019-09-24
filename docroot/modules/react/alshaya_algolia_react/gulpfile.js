const gulp = require('gulp');
const webpack = require('webpack-stream');

gulp.task('default', function() {
  return gulp.src('js/test.js')
    .pipe(webpack( require('./webpack.config.js') ))
    .pipe(gulp.dest('dist/'));
});
