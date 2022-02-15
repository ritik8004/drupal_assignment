var config = require('./gulp/_config');
var gulp = require('gulp');
var bs = require('browser-sync').create();

require('./gulp/styles')(gulp, config, bs);
require('./gulp/scripts')(gulp, config, bs);
require('./gulp/patternlab')(gulp, config, bs);
require('./gulp/svg')(gulp, config, bs);
// Commenting `performance` task for now as it uses `gulp-parker`
// which has a dependency on graceful-fs@3.0.12 and we have
// graceful-fs@4.2.9 to support other packages.
// require('./gulp/performance')(gulp, config, bs);
require('./gulp/watch')(gulp, config, bs);
require('./gulp/default')(gulp, config);
