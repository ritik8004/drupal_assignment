<?php

/**
 * Utility code to be invoked by a shell script to find an ACSF site ID in a
 * JSON returned by ACSF API GET /v1/sites. This is complex to browse a JSON
 * in bash so this php script make is simpler.
 *
 * php get-site-id-from-name.php "<json-string>" "<site-name>"
 */

$json = json_decode($argv[1]);
$name = $argv[2];

foreach ($json->sites as $site) {
  if ($name == $site->site) {
    echo $site->id;
    return;
  }
}

echo 0;
