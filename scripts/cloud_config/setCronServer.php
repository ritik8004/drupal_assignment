<?php

/**
 * @file
 * Utility file.
 */

require_once 'api-v2-auth.php';

$target_env = $argv[1] ?? '';
$server_id = $argv[2] ?? '';

if (empty($target_env) || empty($server_id)) {
  print 'Please specify target environments uuid and server id of the cron server (ex: 6278 for web-6278).';
  exit;
}

$target_url = 'environments/' . $target_env . '/crons';
$target_crons = json_decode(invokeApi($target_url), TRUE);

foreach ($target_crons['_embedded']['items'] ?? [] as $cron) {
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
      'server_id' => $server_id,
    ], JSON_THROW_ON_ERROR),
  ];

  try {
    invokeApi($target_url . '/' . $cron['id'], 'PUT', $options);
    print 'Cron updated for label ' . $cron['label'] . ' on ' . $target_env;
  }
  catch (\Exception $e) {
    print 'Cron NOT updated for label ' . $cron['label'] . ' on ' . $target_env;
    print PHP_EOL;
    print $e->getMessage();
  }

  print PHP_EOL;
}

print PHP_EOL;
