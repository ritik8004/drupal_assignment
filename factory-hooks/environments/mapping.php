<?php
/**
 * @file
 * This file contains the mapping between environment+site and magento /
 * conductor. This only returns machine names of magento / conductor the site
 * must connect to. The mapping between machine names and system url is stored
 * in dedicated files.
 *
 * @see factory-hooks/environments/conductor.php
 * @see factory-hooks/environments/magento.php
 */

/**
 * Get commerce third party settings for specific site + environment combination.
 */
function alshaya_get_commerce_third_party_settings($site, $env) {
  // From the given site and environment, get the magento and conductor
  // environments keys.
  $env_keys = alshaya_get_env_keys($site, $env);

  include_once DRUPAL_ROOT . '/../factory-hooks/environments/magento.php';
  $magentos = alshaya_get_magento_host_data();

  include_once DRUPAL_ROOT . '/../factory-hooks/environments/conductor.php';
  $conductors = alshaya_get_conductor_host_data();

  // This is the format to be merge with $settings.
  $settings = [];
  if (isset($env_keys['conductor']) && isset($conductors[$env_keys['conductor']])) {
    $settings['acq_commerce.conductor'] = $conductors[$env_keys['conductor']];
  }
  if (isset($env_keys['magento']) && isset($magentos[$env_keys['magento']])) {
    $settings['alshaya_api.settings']['magento_host'] = $magentos[$env_keys['magento']];
  }

  return $settings;
}

/**
 * Get the Conductor and Magento names to use for this specific
 * site + environment combination.
 */
function alshaya_get_env_keys($site, $env) {
  $mapping = [
    // Mothercare Kuwait.
    'mckw' => [
      '01test' => [
        'magento' => 'mc_qa',
        'conductor' => 'mc_test',
      ],
      '01uat' => [
        'magento' => 'mc_uat',
        'conductor' => 'mc_uat',
      ],
      '01pprod' => [
        'magento' => 'mc_dev',
        'conductor' => 'mc_pprod',
      ],
      '01live' => [
        'magento' => 'mc_prod',
        'conductor' => 'mc_prod',
      ],
      '01update' => [
        'magento' => 'mc_prod',
        'conductor' => 'mc_prod',
      ],
      // Local, travis, 01dev, 01dev2, 01dev3, 01qa2.
      'default' => [
        'magento' => 'mc_dev',
        'conductor' => 'mc_dev'
      ],
    ],
    // Mothercare KSA.
    'mcksa' => [
      '01test' => [
        'magento' => 'mcksa_qa',
        'conductor' => 'mcksa_test',
      ],
      '01uat' => [
        'magento' => 'mcksa_uat',
        'conductor' => 'mcksa_uat',
      ],
      '01pprod' => [
        'magento' => 'mcksa_uat',
        'conductor' => 'mcksa_pprod',
      ],
      '01live' => [
        'magento' => 'mcksa_prod',
        'conductor' => 'mcksa_prod',
      ],
      '01update' => [
        'magento' => 'mcksa_prod',
        'conductor' => 'mcksa_prod',
      ],
      'default' => [
        'magento' => 'mcksa_dev',
        'conductor' => 'mcksa_dev',
      ],
    ],
    // Mothercare UAE.
    'mcuae' => [],
    // H&M Kuwait.
    'hmkw' => [
      '01qa2' => [
        'magento' => 'hm_qa',
        'conductor' => 'hm_test',
      ],
      '01uat' => [
        'magento' => 'hm_uat',
        'conductor' => 'hm_uat'
      ],
      '01pprod' => [
        'magento' => 'hm_uat',
        'conductor' => 'hm_pprod'
      ],
      // Local, travis, 01dev, 01dev2, 01dev3, 01test, 01uat, 01live, 01update.
      'default' => [
        'magento' => 'hm_qa',
        'conductor' => 'hm_dev',
      ],
    ],
  ];

  // Get the keys following this fallback (from the more specific to the more
  // generic one): site+env > site+default > default+env > default+default.
  $map = [];
  if (isset($mapping[$site][$env])) {
    $map = $mapping[$site][$env];
  }
  elseif (isset($mapping[$site]['default'])) {
    $map = $mapping[$site]['default'];
  }
  elseif (isset($mapping['default'][$env])) {
    $map = $mapping['default'][$env];
  }
  elseif (isset($mapping['default']['default'])) {
    $map = $mapping['default']['default'];
  }

  return $map;
}
