<?php
/**
 * @file
 * Customisation of sites.php for local dev env.
 */

if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $sites['default'] = 'g';
  $sites['local.alshaya.com'] = 'g';
}
