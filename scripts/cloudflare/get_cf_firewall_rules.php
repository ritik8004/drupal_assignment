<?php
// @codingStandardsIgnoreFile

require_once 'common.php';

$domains = get_domains();

$expressions = [];

foreach ($domains as $domain => $zone_id) {
  $rules = get_firewall_rules_for_domain($domain, $zone_id);
  foreach ($rules['result'] ?? [] as $rule) {
    if ($rule['description'] == 'Whitelist') {
      $expressions[$rule['filter']['expression']] = $rule['filter']['expression'];
      print PHP_EOL;
    }
  }
}

print implode(PHP_EOL, $expressions);

print PHP_EOL;
