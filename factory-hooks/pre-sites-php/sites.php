<?php
/**
 * @file
 * Customisation of sites.php for local dev env.
 */

if (!isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $sites['default'] = 'g';
  $sites['127.0.0.1'] = 'g';
  $sites['localr1.alshaya.com'] = 'g';
  $sites['local.non-transac.com'] = 'g';
}
