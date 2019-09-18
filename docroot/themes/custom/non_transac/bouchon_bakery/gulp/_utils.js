var config = require('./_config');
var notify = require('gulp-notify');
var noop = require('gulp-noop');
var parseArgs = require('minimist');
var argv = parseArgs(process.argv.slice(2));
var rename = require('gulp-rename');

module.exports = {
  errorHandler: function (err) {
    'use strict';
    notify.onError({
      title: 'Gulp error in ' + err.plugin,
      message: err.message
    })(err);
  },
  onDev: function (task, other) {
    'use strict';
    if (!other) {
      other = noop();
    }
    if (argv.type === 'undefined') {
      argv.type = 'development';
    }
    return (argv.type !== config.env.prod)
      ? task : other;
  },
  onOther: function (task, other) {
    'use strict';
    if (!other) {
      other = noop();
    }
    return argv.type !== config.env.dev ? task : noop();
  },
  renameRTL: function (path) {
    'use strict';
    return rename(function (path) {
      path.basename = path.basename.replace('.ltr', '.rtl');
    });
  }
};
