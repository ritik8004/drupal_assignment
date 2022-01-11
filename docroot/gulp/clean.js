/**
 * @file
 * Task: Clean.
 */

"use strict";

module.exports = function ({ argv, del }, { blackList }) {
  // Clean task.
  const clean = () => {
    let deletePath = "./build";
    if (argv.path) {
      // Delete blacklist paths.
      const delList = blackList.map((item) => {
        return "!" + deletePath + "/" + item;
      });

      // Clean specified directory passed via arguments.
      if (!argv.path.startsWith("/")) {
        deletePath = deletePath + "/";
      }

      delList.push(deletePath + argv.path);
      deletePath = delList;
    }
    return del(deletePath);
  };

  return clean;
};
