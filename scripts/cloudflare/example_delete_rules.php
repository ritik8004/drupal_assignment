<?php
// @codingStandardsIgnoreFile

/**
 * Example script to delete page rules for specific domain.
 *
 * It contains code to delete all the rules for PPROD environment
 * from factory.alshaya.com domain.
 */

require_once 'common.php';

$zone = get_zone_for_domain('factory.alshaya.com');
$rules = get_page_rules_for_zone($zone)['result'] ?? [];

foreach ($rules as $rule) {
  if (str_contains($rule['targets'][0]['constraint']['value'], '-pprod.factory.alshaya.com/rest')) {
    delete_page_rule_for_zone($zone, $rule['id']);
  }
}
