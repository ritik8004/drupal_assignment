<?php
// @codingStandardsIgnoreFile

require_once 'common.php';

$domain = $argv[1] ?? '';

if (empty($domain)) {
  print 'Please specify the domain to clear cache for.';
  print PHP_EOL;
  exit;
}

//$rules = get_firewall_rules_for_domain($domain);

//print_r($rules);

$name = 'Allow images access for All';
$expression = '(http.request.uri.path contains "sites/g/files")';
$action = 'allow';
print_r(create_firewall_rule($domain, $name, $expression, $action));

print PHP_EOL;
