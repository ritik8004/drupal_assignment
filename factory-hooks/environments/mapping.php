<?php
// @codingStandardsIgnoreFile

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
  // From the given site and environment, get the magento and conductor
  // environments keys.
  $env_keys = alshaya_get_env_keys($site_code, $country_code, $env);

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
function alshaya_get_env_keys($site_code, $country_code, $env) {
  $site = $site_code . $country_code;

  // Default mapping is following:
  // dev, dev2, dev3, test, qa2: QA/Test.
  // uat: UAT.
  // pprod/live: Prod.
  // To override this default behavior, define the specific mapping in the
  // $mapping array following this structure:
  // '<site-code>' => [
  //   '01<env>' => [
  //     'magento' => '<magento-key>',
  //     'conductor' => '<conductor-key>'
  //   ]
  // ]
  // <site-code> is the ACSF site id (mckw, hmsa, bbwae, ...).
  // <env> is the ACSF environment name (dev, dev2, qa, pprod, ...).
  // <magento-key> is a MDC environment listed in magento.php.
  // <conductor-key> is an ACM instance listed in conductor.php.

  $default = [
    '01dev' => 'qa',
    '01dev2' => 'qa',
    '01dev3' => 'qa',
    '01test' => 'qa',
    '01qa2' => 'qa',
    '01uat' => 'uat',
    '01pprod' => 'prod',
    '01live' => 'prod',
    'local' => 'qa',
    'travis' => 'qa'
  ];

  // Fill this variable to override the default mapping.
  $mapping = [
    'hmkw' => [
      '01dev3' => [
        'magento' => 'hm_mapp',
        'conductor' => 'hmkw_mapp',
      ],
      '01qa2' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmkw_uat',
      ],
    ],
    'hmsa' => [
      '01dev3' => [
        'magento' => 'hm_mapp',
        'conductor' => 'hmsa_mapp',
      ],
      '01qa2' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmsa_uat',
      ],
    ],
    'hmae' => [
      '01dev3' => [
        'magento' => 'hm_mapp',
        'conductor' => 'hmae_mapp',
      ],
      '01qa2' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmae_uat',
      ],
    ],
    'flkw' => [
      '01dev2' => [
        'magento' => 'fl_uat',
        'conductor' => 'flkw_uat',
      ],
    ],
    'flsa' => [
      '01dev2' => [
        'magento' => 'fl_uat',
        'conductor' => 'flsa_uat',
      ],
    ],
    'flae' => [
      '01dev2' => [
        'magento' => 'fl_uat',
        'conductor' => 'flae_uat',
      ],
    ],
  ];

  // All 01update should match 01live.
  // Update array to set 01update if 01live is set.
  foreach ($mapping as $key => $value) {
    if (isset($mapping[$key]['01live'])) {
      $mapping[$key]['01update'] = $mapping[$key]['01live'];
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

  // If MDC or Conductor mapping is not defined, use the default mapping
  // pattern.
  if (empty($map['magento'])) {
    $map['magento'] = $site_code . '_' . $default[$env];
  }
  if (empty($map['conductor'])) {
    $map['conductor'] = $site_code . $country_code . '_' . $default[$env];
  }

  return $map;
}
