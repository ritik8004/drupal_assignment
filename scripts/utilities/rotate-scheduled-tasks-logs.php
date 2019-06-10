<?php

/**
 * This file aims to rotate custom logs on Alshaya servers. Scheduled tasks on
 * ACE are configured to log on custom log files which the pattern is
 * "alshaya-<something>.log". This script will identifies the files matching
 * the pattern and which are bigger than ~10Mo.
 *
 * Usage: php rotate-scheduled-tasks-logs.php "/path/to/log/directory/"
 */

$directory = $argv[1];
if (empty($directory)) {
  return;
}

if ($directory[strlen($directory)-1] !== '/') {
  $directory .= '/';
}

foreach (array_diff(scandir($directory), array('..', '.')) as $item) {
  // We only deal with files.
  if (!is_file($directory . $item)) {
    continue;
  }

  // we only deal with files which the prefix is "alshaya-".
  if (substr($item, 0, 8) != 'alshaya-' || substr($item, -3, 3) != 'log') {
    continue;
  }

  // We check the file size so we only rotate when files become too big.
  if (filesize($directory . $item) < 10000000) {
    echo "$item is only " . filesize($directory . $item) . ". Skipping rotation.";
    continue;
  }

  $file_without_ext = substr($item, 0, -4);

  // If there is already an archive, we delete it, no need to keep too much
  // history.
  if (file_exists($directory . $item . '.gz')) {
    unlink($directory . $item . '.gz');
  }

  // Create a new archive with the file.
  shell_exec('gzip ' . $directory . $item);

  echo "$item has been rotated.";
}
