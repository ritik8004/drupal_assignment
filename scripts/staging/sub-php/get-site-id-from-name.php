<?php

$json = json_decode($argv[1]);
$name = $argv[2];

foreach ($json->sites as $site) {
  if ($name == $site->site) {
    echo $site->id;
    return;
  }
}

echo 0;