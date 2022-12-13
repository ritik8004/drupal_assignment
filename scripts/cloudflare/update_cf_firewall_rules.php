<?php
// @codingStandardsIgnoreFile

require_once 'common.php';

$domains = get_domains();

$new_expression = 'http.user_agent eq "rtbhouse-parser" or http.user_agent contains "facebookexternalhit" or http.user_agent eq "algolia/user-content-v1" or http.request.uri contains "/feed.xml" or http.request.uri contains "themes/custom/transac/" or http.request.uri contains "sites/g/files/" or (http.user_agent contains "GuzzleHttp" and http.user_agent contains "PHP") or http.user_agent contains "Alshaya/Middleware"';
$new_challenge_expression = 'not cf.client.bot and not ip.geoip.country in {"KW" "SA" "AE" "EG" "BH" "JO" "QA" "FR" "GB" "IN" "US"}';

foreach ($domains as $domain => $zone_id) {
  print $domain . PHP_EOL;
  sleep(30);

  $rules = get_firewall_rules_for_domain($domain, $zone_id);
  foreach ($rules['result'] ?? [] as $rule) {
    if ($rule['description'] == 'Whitelist') {
      $rule['filter']['expression'] = $new_expression;
      print_r(update_firewall_filter_for_domain($zone_id, [$rule['filter']]));

      $rule['description'] = 'Allowed List';
      print_r(update_firewall_rules_for_domain($zone_id, [$rule]));

      print PHP_EOL;
    }
    elseif ($rule['description'] == 'Allow images access for All') {
      print 'Please delete the workaround rule for domain: ' . $domain;
      sleep(15);
      print PHP_EOL;
    }
    elseif ($rule['description'] == 'Undesired traffic - Challenge except'
      && $rule['filter']['expression'] != $new_challenge_expression) {
      $rule['filter']['expression'] = $new_challenge_expression;
      print_r(update_firewall_filter_for_domain($zone_id, [$rule['filter']]));
      print PHP_EOL;
    }
  }

  print PHP_EOL;
}

print PHP_EOL;
