<?php

/**
 * @file
 * Utility file.
 */

require_once 'api-v2-auth.php';

$source_env = $argv[1] ?? '';
$target_env = $argv[2] ?? '';

if (empty($source_env) || empty($target_env)) {
  print 'Please specify source and target environments uuid.';
  exit;
}

$source_url = 'environments/' . $source_env . '/crons';
$source_crons = json_decode(invokeApi($source_url), TRUE);

$target_url = 'environments/' . $target_env . '/crons';
foreach ($source_crons['_embedded']['items'] ?? [] as $cron) {
  if (empty($cron['label'])) {
    print 'Skipped as label is empty.';
    print PHP_EOL . PHP_EOL;
    continue;
  }

  $options = [
    'headers' => [
      'Content-Type' => 'application/json',
    ],
    'body' => json_encode([
      'command' => $cron['command'],
      'frequency' => implode(' ', [
        $cron['minute'],
        $cron['hour'],
        $cron['day_month'],
        $cron['month'],
        $cron['month'],
      ]),
      'label' => $cron['label'],
      'server_id' => NULL,
    ], JSON_THROW_ON_ERROR),
  ];

  try {
    invokeApi($target_url, 'POST', $options);
    print 'Cron created for label ' . $cron['label'] . ' on ' . $target_env;
  }
  catch (\Exception $e) {
    print 'Cron NOT created for label ' . $cron['label'] . ' on ' . $target_env;
    print PHP_EOL;
    print $e->getMessage();
  }

  print PHP_EOL;
}

print PHP_EOL;
