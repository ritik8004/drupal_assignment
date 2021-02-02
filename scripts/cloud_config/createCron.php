<?php
// @codingStandardsIgnoreFile

require_once 'api-v2-auth.php';

// Sequence: UUID COMMAND FREQUENCY LABEL SERVER_ID

$env_uuid = $argv[1] ?? '';
$cron_command = $argv[2] ?? '';
$cron_frequency = $argv[3] ?? '';
$cron_label = $argv[4] ?? '';

// To be used only on production.
$cron_server_id = $argv[5] ?? '';

if (empty($env_uuid) || empty($cron_command) || empty($cron_frequency) || empty($cron_label)) {
  print 'Please check code to know the arguments and provide all of them.';
  print PHP_EOL;
  die();
}

$cron = [
  'command' => $cron_command,
  'frequency' => $cron_frequency,
  'label' => $cron_label,
  'server_id' => empty($cron_server_id) ? NULL : $cron_server_id,
];

$options = [
  'headers' => [
    'Content-Type' => 'application/json',
  ],
  'body' => json_encode($cron),
];


$url = 'environments/' . $env_uuid . '/crons';
try {
  invokeApi($url, 'POST', $options);
  print 'Cron created';
}
catch (\Exception $e) {
  print 'Failed to create cron - message: ' . $e->getMessage();
}

print PHP_EOL;
