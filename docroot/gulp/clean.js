/**
 * @file
 * Task: Clean.
 */

"use strict";

module.exports = function ({ argv, del }) {
  // Clean task.
  const clean = () => {
    let deletePath = "./build";
    if (argv.path) {
      // Clean specified directory passed via arguments.
      if (!argv.path.startsWith("/")) {
        deletePath = deletePath + "/";
      }
      deletePath = deletePath + argv.path;
    }
    return del(deletePath);
  };

  return clean;
};
