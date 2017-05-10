<?php

/**
 * @file
 * Example implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Configure your hash salt here.
// $settings['hash_salt'] = '';

require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/blt.settings.php';

// Travis case.
if (empty($config_directories)) {
  // Configuration directories.
  $dir = dirname(DRUPAL_ROOT);
  $config_directories['sync'] = $dir . "/config/$site_dir";
}

// Default credentials.
$settings['alshaya_custom_shield_default_user'] = 'alshaya_shield';
$settings['alshaya_custom_shield_default_pass'] = 'AS_S';

// Conductor settings.
$config['acq_commerce.conductor']['url_agent'] = 'https://agent.dev.acm.acquia.io/';
$config['acq_commerce.conductor']['url_ingest'] = 'https://ingest.dev.acm.acquia.io/';
$config['acq_commerce.conductor']['verify_ssl'] = FALSE;
$config['acq_commerce.conductor']['base_url'] = 'https://sylvain-kolpzxy-pgplwghmuzhmm.us.magentosite.cloud';
$config['acq_commerce.conductor']['media_path'] = 'media/catalog/product';

// Recaptcha settings.
$config['recaptcha.settings']['site_key'] = '6Le93BsUAAAAAMOiJ5wrk4ICF0N-dLs6iM_eR4di';
$config['recaptcha.settings']['secret_key'] = '6Le93BsUAAAAABQ0RMy0TIFuKasg3uz8hqVl4c6n';
