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
  global $magentos;

  include_once DRUPAL_ROOT . '/../factory-hooks/environments/conductor.php';
  global $conductors;

  // This is the format to be merge with $settings.
  $settings = [];
  if (isset($env_keys['conductor']) && isset($conductors[$env_keys['conductor']])) {
    $settings['acq_commerce.conductor'] = $conductors[$env_keys['conductor']];
  }
  if (isset($env_keys['magento']) && isset($magentos[$env_keys['magento']])) {
    $settings['alshaya_api.settings']['magento_host'] = $magentos[$env_keys['magento']]['url'];
    if (isset($magentos[$env_keys['magento']]['magento_secrets'])) {
      $settings['alshaya_api.settings'] += $magentos[$env_keys['magento']]['magento_secrets'];
    }

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
      '01live' => [
        'magento' => 'mc_prod',
        'conductor' => 'mckw_prod',
      ],
      'default' => [
        'magento' => 'mc_qa',
        'conductor' => 'mckw_test',
      ],
    ],
    // Mothercare SA.
    'mcsa' => [
      '01uat' => [
        'magento' => 'mc_uat',
        'conductor' => 'mcsa_uat',
      ],
      '01live' => [
        'magento' => 'mc_prod',
        'conductor' => 'mcsa_prod',
      ],
      'default' => [
        'magento' => 'mc_qa',
        'conductor' => 'mcsa_test',
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
      'default' => [
        'magento' => 'mc_qa',
        'conductor' => 'mcae_test',
      ],
    ],
    // H&M Kuwait.
    'hmkw' => [
      '01uat' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmkw_uat'
      ],
      '01live' => [
        'magento' => 'hm_prod',
        'conductor' => 'hmkw_prod'
      ],
      'default' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmkw_test',
      ],
      '01dev3' => [
        'magento' => 'hm_mapp',
        'conductor' => 'hmkw_mapp',
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
      '01dev3' => [
        'magento' => 'hm_mapp',
        'conductor' => 'hmsa_mapp',
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
      '01dev3' => [
        'magento' => 'hm_mapp',
        'conductor' => 'hmae_mapp',
      ],
    ],
    // H&M EG.
    'hmeg' => [
      'default' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmeg_test',
      ],
      '01uat' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmeg_uat',
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
        'conductor' => 'bbwae_prod',
      ],
    ],
    // Pottery Barn KW.
    'pbkw' => [
      '01dev2' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbkw_dev2',
      ],
      'default' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbkw_test',
      ],
      '01uat' => [
        'magento' => 'pb_uat',
        'conductor' => 'pbkw_uat',
      ],
      '01live' => [
        'magento' => 'pb_prod',
        'conductor' => 'pbkw_prod',
      ],
    ],
    // Pottery Barn SA.
    'pbsa' => [
      '01dev2' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbsa_dev2',
      ],
      'default' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbsa_test',
      ],
      '01uat' => [
        'magento' => 'pb_uat',
        'conductor' => 'pbsa_uat',
      ],
      '01live' => [
        'magento' => 'pb_prod',
        'conductor' => 'pbsa_prod',
      ],
    ],
    // Pottery Barn AE.
    'pbae' => [
      '01dev2' => [
        'magento' => 'pb_uat',
        'conductor' => 'pbae_uat',
      ],
      'default' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbae_test',
      ],
      '01uat' => [
        'magento' => 'pb_uat',
        'conductor' => 'pbae_uat',
      ],
      '01live' => [
        'magento' => 'pb_prod',
        'conductor' => 'pbae_prod',
      ],
    ],
    // Victoria Secret KW.
    'vskw' => [
      '01live' => [
        'magento' => 'vs_prod',
        'conductor' => 'vskw_prod',
      ],
      '01uat' => [
        'magento' => 'vs_uat',
        'conductor' => 'vskw_uat',
      ],
      'default' => [
        'magento' => 'vs_qa',
        'conductor' => 'vskw_test',
      ],
    ],
    // Victoria Secret SA.
    'vssa' => [
      '01live' => [
        'magento' => 'vs_prod',
        'conductor' => 'vssa_prod',
      ],
      '01uat' => [
        'magento' => 'vs_uat',
        'conductor' => 'vssa_uat',
      ],
      'default' => [
        'magento' => 'vs_qa',
        'conductor' => 'vssa_test',
      ],
    ],
    // Victoria Secret AE.
    'vsae' => [
      '01live' => [
        'magento' => 'vs_prod',
        'conductor' => 'vsae_prod',
      ],
      '01uat' => [
        'magento' => 'vs_uat',
        'conductor' => 'vsae_uat',
      ],
      'default' => [
        'magento' => 'vs_qa',
        'conductor' => 'vsae_test',
      ],
    ],
    // Foot Locker KW.
    'flkw' => [
      '01uat' => [
        'magento' => 'fl_uat',
        'conductor' => 'flkw_uat',
      ],
      'default' => [
        'magento' => 'fl_qa',
        'conductor' => 'flkw_test',
      ],
    ],
    // Foot Locker SA.
    'flsa' => [
      '01uat' => [
        'magento' => 'fl_uat',
        'conductor' => 'flsa_uat',
      ],
      'default' => [
        'magento' => 'fl_qa',
        'conductor' => 'flsa_test',
      ],
    ],
    // Foot Locker AE.
    'flae' => [
      '01uat' => [
        'magento' => 'fl_uat',
        'conductor' => 'flae_uat',
      ],
      'default' => [
        'magento' => 'fl_qa',
        'conductor' => 'flae_test',
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

  // By default, map pprod with prod acm+mdc.
  foreach ($mapping as $site_code => $envs) {
    if (isset($mapping[$site_code]['01live']) && !isset($mapping[$site_code]['01pprod'])) {
      $mapping[$site_code]['01pprod'] = $mapping[$site_code]['01live'];
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
