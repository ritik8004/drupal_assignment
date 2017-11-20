<?php

/**
 * Get specific settings for specific site + environment combination.
 */
function alshaya_get_specific_settings($site, $env) {
  // From the given site and environment, get the magento and conductor
  // environments keys.
  $env_keys = alshaya_get_env_keys($site, $env);

  include_once DRUPAL_ROOT . '/../factory-hooks/environments/magento.php';
  $magentos = alshaya_get_magento_host_data();

  include_once DRUPAL_ROOT . '/../factory-hooks/environments/conductor.php';
  $conductors = alshaya_get_conductor_host_data();

  // This is the format to be merge with $settings.
  return [
    'acq_commerce.conductor' => $conductors[$env_keys['conductor']],
    'alshaya_api.settings' => [
      'magento_host' => $magentos[$env_keys['magento']],
    ],
  ];
}

/**
 * Get the Conductor and Magento names to use for this specific
 * site + environment combination.
 */
function alshaya_get_env_keys($site, $env) {
  $mapping = [
    // Mothercare Kuwait.
    'mckw' => [
      'local' => [
        'magento' => 'mc_dev',
        'conductor' => 'mc_dev'
      ],
      'default' => [
        'magento' => 'mckw_magento_default',
      ],
    ],
    // Mothercare UAE.
    'mcuae' => [],
    // H&M Kuwait.
    'hmkw' => [
      'local' => [
        'magento' => 'hm_qa',
        'conductor' => 'hm_dev',
      ],
    ],
    // Default.
    'default' => [
      'local' => [
        'magento' => 'default_magento_local',
        'conductor' => '',
      ],
      'default' => [
        'magento' => 'default_magento_default',
      ],
    ],
  ];

  // Get the keys following this fallback (from the more specific to the more
  //generic one): site+env > site+default > default+env > default+default.
  $map = $mapping['default']['default'];
  if (isset($mapping[$site][$env])) {
    $map = $mapping[$site][$env];
  }
  elseif (isset($mapping[$site]['default'])) {
    $map = $mapping[$site]['default'];
  }
  elseif (isset($mapping['default'][$env])) {
    $map = $mapping['default'][$env];
  }

  return $map;
}
