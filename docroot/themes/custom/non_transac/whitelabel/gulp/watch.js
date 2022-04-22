module.exports = function (gulp, config, bs) {
  'use strict';

  gulp.task('watch', function () {
    bs.init(config.browserSync);

    gulp.watch(
      config.styles.source,
      gulp.series(
        gulp.parallel('styles-lint'),
        gulp.parallel('styles')
      )
    );

    gulp.watch(
      config.scripts.source,
      gulp.series(
        gulp.parallel('styles-lint', 'scripts-lint'),
        gulp.parallel('styles', 'scripts')
      )
    );

    gulp.watch(
      config.patternlab.source,
      gulp.series('patternlab')
    );
  });
};
