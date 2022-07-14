<?php
// @codingStandardsIgnoreFile

require_once 'common.php';


foreach (get_domains() as $domain => $zone) {
  print $domain . PHP_EOL;
  print_r(always_online_update($zone, 'off'));
}

print PHP_EOL;
