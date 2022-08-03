<?php
// phpcs:ignoreFile

require_once 'common.php';

$domain = $argv[1] ?? '';
$theme = $argv[2] ?? '';
$magento_site_code = $argv[3] ?? '';

if (empty($domain)) {
  print 'Please specify the domain to create rules for.';
  print PHP_EOL;
  exit;
}

if (empty($theme)) {
  print 'Please specify the theme name to use for favicon rule.';
  print PHP_EOL;
  exit;
}

if (empty($magento_site_code)) {
  print 'Please specify the magento site code to use for proxy rules.';
  print PHP_EOL;
  exit;
}

$domain_clean = str_replace('www.', '', $domain);

$rules = [];

// V2.
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
      "value" => $magento_site_code . ".store.alshaya.com",
    ],
  ],
  "priority" => "97",
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
  "priority" => "98",
  "status" => "active",
];

$rules['cache_react_dist'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/modules/react/*/dist/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => 0,
    ],
    [
      "id" => "edge_cache_ttl",
      "value" => 2_678_400,
    ],
  ],
  "priority" => 96,
  "status" => "active",
];

// ALX InStorE.
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

// General.
$rules['favicon'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/favicon.ico",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "forwarding_url",
      "value" => [
        "url" => "https://" . $domain . "/themes/custom/transac/" . $theme . "/favicon.ico",
        "status_code" => "301",
      ],
    ],
  ],
  "status" => "active",
];

$rules['maintenance_rule'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "forwarding_url",
      "value" => [
        "url" => "https://monitoring.factory.alshaya.com/maintenance.html",
        "status_code" => "302",
      ],
    ],
  ],
  "status" => "disabled",
];

$rules['https'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "http://*" . $domain_clean . "/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "always_use_https",
    ],
  ],
  "priority" => "100",
  "status" => "active",
];

$rules['_cf_cache_bypass'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/*?*_cf_cache_bypass=1*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "cache_level",
      "value" => "bypass",
    ],
  ],
  "priority" => "80",
  "status" => "active",
];

$rules['node_edit'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/*/node/*/edit*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "disable_security",
    ],
    [
      "id" => "browser_cache_ttl",
      "value" => "14400",
    ],
  ],
  "priority" => "13",
  "status" => "active",
];

$rules['admin_config'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/*/admin/config/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "disable_security",
    ],
  ],
  "priority" => "12",
  "status" => "active",
];

$rules['cron'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/cron.php",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => 0,
    ],
    [
      "id" => "cache_level",
      "value" => "bypass",
    ],
  ],
  "priority" => "11",
  "status" => "active",
];

$rules['themes_custom'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/themes/custom/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => 0,
    ],
    [
      "id" => "cache_level",
      "value" => "cache_everything",
    ],
  ],
  "priority" => "10",
  "status" => "active",
];

$rules['assets_vendor'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/core/assets/vendor/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => 0,
    ],
    [
      "id" => "cache_level",
      "value" => "cache_everything",
    ],
  ],
  "priority" => "9",
  "status" => "active",
];

$rules['files'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/sites/*/files/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => 0,
    ],
    [
      "id" => "cache_level",
      "value" => "cache_everything",
    ],
  ],
  "priority" => "8",
  "status" => "active",
];

$rules['profile_themes'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/profiles/custom/*/themes/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => 0,
    ],
    [
      "id" => "cache_level",
      "value" => "cache_everything",
    ],
  ],
  "priority" => "6",
  "status" => "active",
];

$rules['users'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/*/user/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => 0,
    ],
    [
      "id" => "cache_level",
      "value" => "bypass",
    ],
  ],
  "priority" => "5",
  "status" => "active",
];

$rules['user_register'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/user/register*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "security_level",
      "value" => "high",
    ],
    [
      "id" => "cache_level",
      "value" => "bypass",
    ],
  ],
  "priority" => "4",
  "status" => "active",
];

$rules['batch'] = [
  "id" => "9b88907f08f3f0d98706f79234038e4e",
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/*batch?*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "cache_level",
      "value" => "bypass",
    ],
    [
      "id" => "sort_query_string_for_cache",
      "value" => "off",
    ],
  ],
  "priority" => "2",
  "status" => "active",
];

$rules['session'] = [
  "targets" => [
    [
      "target" => "url",
      "constraint" => [
        "operator" => "matches",
        "value" => "*" . $domain_clean . "/*",
      ],
    ],
  ],
  "actions" => [
    [
      "id" => "browser_cache_ttl",
      "value" => "1200",
    ],
    [
      "id" => "cache_level",
      "value" => "cache_everything",
    ],
    [
      "id" => "edge_cache_ttl",
      "value" => "1200",
    ],
    [
      "id" => "bypass_cache_on_cookie",
      "value" => "SSESS.*|SESS.*|NO_CACHE|PERSISTENT_LOGIN_.*",
    ],
    [
      "id" => "disable_apps",
    ],
  ],
  "priority" => "1",
  "status" => "active",
];

$zone = get_zone_for_domain($domain);

if (empty($zone)) {
  print PHP_EOL;
  die();
}

$existing_rules = get_page_rules_for_zone($zone)['result'] ?? [];

// Do not create rules again.
// We will have separate script for update.
foreach ($rules as $key => $rule) {
  $check_target = strtolower(json_encode($rule['targets']));

  foreach ($existing_rules as $existing_rule) {
    if ($check_target === strtolower(json_encode($existing_rule['targets'], JSON_THROW_ON_ERROR))) {
      unset($rules[$key]);
      break;
    }
  }

  print PHP_EOL;
}

foreach ($rules as $rule) {
  print_r(create_page_rule_for_zone($zone, $rule));
}
