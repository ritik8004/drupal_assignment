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
function alshaya_get_commerce_third_party_settings($site_code, $country_code, $env) {
  $site = $site_code . $country_code;

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
    $settings['alshaya_api.settings']['magento_host'] = $magentos[$env_keys['magento']]['url'];

    $settings += $magentos['default'][$country_code];
    if (isset($magentos[$env_keys['magento']][$country_code])) {
      $settings = array_replace_recursive($settings, $magentos[$env_keys['magento']][$country_code]);
    }
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
      '01uat' => [
        'magento' => 'mc_uat',
        'conductor' => 'mckw_uat',
      ],
      '01pprod' => [
        'magento' => 'mc_dev',
        'conductor' => 'mckw_pprod',
      ],
      '01live' => [
        'magento' => 'mc_prod',
        'conductor' => 'mckw_prod',
      ],
      '01test' => [
        'magento' => 'mc_qa',
        'conductor' => 'mckw_test',
      ],
      // Local, travis, 01dev, 01dev2, 01dev3, 01qa2.
      'default' => [
        'magento' => 'mc_dev',
        'conductor' => 'mc_v2',
      ],
    ],
    // Mothercare SA.
    'mcsa' => [
      '01uat' => [
        'magento' => 'mc_uat',
        'conductor' => 'mcsa_uat',
      ],
      '01pprod' => [
        'magento' => 'mc_uat',
        'conductor' => 'mcsa_pprod',
      ],
      '01live' => [
        'magento' => 'mc_prod',
        'conductor' => 'mcsa_prod',
      ],
      '01test' => [
        'magento' => 'mc_qa',
        'conductor' => 'mcsa_test',
      ],
      // Local, travis, 01dev, 01dev2, 01dev3, 01qa2.
      'default' => [
        'magento' => 'mc_dev',
        'conductor' => 'mc_v2',
      ],
    ],
    // Mothercare UAE.
    'mcae' => [
      '01uat' => [
        'magento' => 'mc_uat',
        'conductor' => 'mcae_uat',
      ],
      '01live' => [
        'magento' => 'mc_prod',
        'conductor' => 'mcae_prod',
      ],
      '01test' => [
        'magento' => 'mc_qa',
        'conductor' => 'mcae_test',
      ],
      // Local, travis, 01dev, 01dev2, 01dev3, 01qa2.
      'default' => [
        'magento' => 'mc_dev',
        'conductor' => 'mc_v2',
      ],
    ],
    // H&M Kuwait.
    'hmkw' => [
      '01uat' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmkw_uat'
      ],
      '01pprod' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmkw_pprod'
      ],
      '01live' => [
        'magento' => 'hm_prod',
        'conductor' => 'hmkw_prod'
      ],
      // Local, travis, 01dev, 01dev2, 01dev3.
      'default' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmkw_test',
      ],
    ],
    // H&M SA.
    'hmsa' => [
      'default' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmsa_test',
      ],
      '01uat' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmsa_uat',
      ],
      '01live' => [
        'magento' => 'hm_prod',
        'conductor' => 'hmsa_prod'
      ],
    ],
    // H&M AE.
    'hmae' => [
      'default' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmae_test',
      ],
      '01uat' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmae_uat',
      ],
      '01live' => [
        'magento' => 'hm_prod',
        'conductor' => 'hmae_prod'
      ],
    ],
    // BathBodyWorks KW.
    'bbwkw' => [
      'default' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwkw_test',
      ],
      '01uat' => [
        'magento' => 'bbw_uat',
        'conductor' => 'bbwkw_uat',
      ],
      '01live' => [
        'magento' => 'bbw_prod',
        'conductor' => 'bbwkw_prod'
      ],
    ],
    // BathBodyWorks SA.
    'bbwsa' => [
      'default' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwsa_test',
      ],
      '01uat' => [
        'magento' => 'bbw_uat',
        'conductor' => 'bbwsa_uat',
      ],
      '01live' => [
        'magento' => 'bbw_prod',
        'conductor' => 'bbwsa_prod'
      ],
    ],
    // BathBodyWorks AE.
    'bbwae' => [
      'default' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwae_test',
      ],
      '01uat' => [
        'magento' => 'bbw_uat',
        'conductor' => 'bbwae_uat',
      ],
      '01live' => [
        'magento' => 'bbw_prod',
        'conductor' => 'bbwae_prod'
      ],
    ],
    // Pottery Barn KW.
    'pbkw' => [
      'default' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbkw_test',
      ],
    ],
    // Pottery Barn SA.
    'pbsa' => [
      'default' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbsa_test',
      ],
    ],
    // Pottery Barn AE.
    'pbae' => [
      'default' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbae_test',
      ],
    ],
    // Victoria Secret KW.
    'vskw' => [
      '01uat' => [
        'magento' => 'vs_uat',
        'conductor' => 'vskw_uat',
      ],
      'default' => [
        'magento' => 'vs_qa',
        'conductor' => 'vskw_test_v1',
      ],
    ],
    // Victoria Secret SA.
    'vssa' => [
      '01uat' => [
        'magento' => 'vs_uat',
        'conductor' => 'vssa_uat',
      ],
      'default' => [
        'magento' => 'vs_qa',
        'conductor' => 'vssa_test_v1',
      ],
    ],
    // Victoria Secret AE.
    'vsae' => [
      '01uat' => [
        'magento' => 'vs_uat',
        'conductor' => 'vsae_uat',
      ],
      'default' => [
        'magento' => 'vs_qa',
        'conductor' => 'vsae_test_v1',
      ],
    ],
  ];

  // All 01update should match 01live.
  // Update array to set 01update if 01live is set.
  foreach ($mapping as $site_code => $envs) {
    if (isset($mapping[$site_code]['01live'])) {
      $mapping[$site_code]['01update'] = $mapping[$site_code]['01live'];
    }
  }

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
