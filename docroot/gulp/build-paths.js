/**
 * @file
 * Tasks: Build Path.
 */

"use strict";

module.exports = function ({ src, fs }, { buildPath }, { relativePaths }) {
  const { scripts, resultJson } = buildPath;
  const buildPathList = [];

  // Custom task to fetch built JS relative paths.
  const extractBuildPaths = () => {
    return src(scripts).pipe(relativePaths(buildPathList));
  };

  // Custom task to write file paths to JSON.
  const outputBuildPaths = async () => {
    const content = {
      source: buildPathList || [],
    };

    fs.writeFile(resultJson, JSON.stringify(content), (err) => {
      if (err) {
        console.error(err);
        return;
      }
    });
  };

  return { extractBuildPaths, outputBuildPaths };
};
