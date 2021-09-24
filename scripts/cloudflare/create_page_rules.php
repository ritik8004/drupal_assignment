<?php
// @codingStandardsIgnoreFile

require_once 'common.php';

$domain = $argv[1] ?? '';

if (empty($domain)) {
  print 'Please specify the domain to create rules for.';
  print PHP_EOL;
  exit;
}

$zone = get_zone_for_domain($domain);

if (empty($zone)) {
  print PHP_EOL;
  die();
}

$domain_clean = str_replace('www.', '', $domain);

$rules['alx_v2'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/*/spc/resume-cart-from-agent*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => 0,
    ],
  ],
  "priority" => 95,
  "status" => "active",
];

$rules['commerce_v2'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/rest/*_*/V1/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "resolve_override",
      "value" => "commerce." . $domain_clean . "",
    ],
    [
      "id" => "host_header_override",
      "value" => "ri.store.alshaya.com",
    ],
  ],
  "priority" => 97,
  "status" => "active",
];

$rules['commerce_v2_callback'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/*/spc/payment-callback/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => 0,
    ],
  ],
  "priority" => 98,
  "status" => "active",
];

$existing_rules = get_page_rules_for_zone($zone)['result'] ?? [];

// Do not create rules again.
// We will have separate script for update.
foreach ($rules as $key => $rule) {
  $check_target = strtolower(json_encode($rule['targets']));

  foreach ($existing_rules as $existing_rule) {
    if ($check_target === strtolower(json_encode($existing_rule['targets']))) {
      unset($rules[$key]);
      break;
    }
  }

  print PHP_EOL;
}

foreach ($rules as $rule) {
  print_r(create_page_rule_for_zone($zone, $rule));
}

print PHP_EOL;
