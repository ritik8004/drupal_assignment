<?php
// phpcs:ignoreFile

require_once 'common.php';

$actions = [
  [
    'id' => 'browser_cache_ttl',
    'value' => 14400,
  ],
  [
    'id' => 'edge_cache_ttl',
    'value' => 31536000,
  ],
];


foreach (get_domains() as $domain => $zone) {
  print $domain . PHP_EOL;

  $rules = get_page_rules_for_zone($zone)['result'] ?? [];
  foreach ($rules as $rule) {
    if (str_contains($rule['targets'][0]['constraint']['value'], "$domain/assets/*")) {
      $rule['actions'] = array_merge($rule['actions'], $actions);
      $response = update_page_rule_for_zone($zone, $rule);
      if ($response['errors']) {
        print_r($response);
      }
    }
  }

  print PHP_EOL;
}

print PHP_EOL;
