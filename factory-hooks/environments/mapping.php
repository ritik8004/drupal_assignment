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

    // This is specific to HM right now but since it is tied to Magento env
    // we set in magento.php and use like this.
    if (isset($magentos[$env_keys['magento']]['pims_base_url'])) {
      $settings['alshaya_hm_images.settings']['pims_base_url'] = $magentos[$env_keys['magento']]['pims_base_url'];
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
  //   '<env>' => [
  //     'magento' => '<magento-key>',
  //     'conductor' => '<conductor-key>'
  //   ]
  // ]
  // <site-code> is the ACSF site id (mckw, hmsa, bbwae, ...).
  // <env> is the ACSF environment name (dev, dev2, qa, pprod, ...).
  // <magento-key> is a MDC environment listed in magento.php.
  // <conductor-key> is an ACM instance listed in conductor.php.

  $default = [
    'dev' => 'qa',
    'dev2' => 'qa',
    'dev3' => 'qa',
    'test' => 'qa',
    'qa2' => 'qa',
    'uat' => 'uat',
    'pprod' => 'prod',
    'live' => 'prod',
    'local' => 'qa',
    'travis' => 'qa'
  ];

  // Fill this variable to override the default mapping.
  $mapping = [
    'hmkw' => [
      'dev' => [
        'magento' => 'hm_upgrade',
        'conductor' => 'hmkw_dev',
      ],
      'dev2' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmkw_dev2',
      ],
    ],
    'hmsa' => [
      'dev' => [
        'magento' => 'hm_upgrade',
        'conductor' => 'hmsa_dev',
      ],
      'dev2' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmsa_dev2',
      ],
    ],
    'hmae' => [
      'dev' => [
        'magento' => 'hm_upgrade',
        'conductor' => 'hmae_dev',
      ],
      'dev2' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmae_dev2',
      ],
    ],
    'hmeg' => [
      'local' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmeg_dev2',
      ],
      'dev2' => [
        'magento' => 'hm_uat',
        'conductor' => 'hmeg_dev2',
      ],
    ],
    'flsa' => [
      'dev' => [
        'magento' => 'fl_qa',
        'conductor' => 'flsa_dev',
      ],
      'local' => [
        'magento' => 'fl_uat',
        'conductor' => 'flsa_dev2',
      ],
      'dev2' => [
        'magento' => 'fl_uat',
        'conductor' => 'flsa_dev2',
      ],
    ],
    'bbwae' => [
      'dev' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwae_dev',
      ],
      'local' => [
        'magento' => 'bbw_uat',
        'conductor' => 'bbwae_dev2',
      ],
      'dev2' => [
        'magento' => 'bbw_uat',
        'conductor' => 'bbwae_dev2',
      ],
    ],
    'bbwsa' => [
      'dev2' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwsa_dev2',
      ],
    ],
    'mckw' => [
      'dev' => [
        'magento' => 'mc_upgrade',
        'conductor' => 'mckw_dev',
      ],
      'dev2' => [
        'magento' => 'mc_qa',
        'conductor' => 'mckw_dev2',
      ],
    ],
    'mcsa' => [
      'dev' => [
        'magento' => 'mc_qa',
        'conductor' => 'mcsa_dev',
      ],
    ],
    'pbkw' => [
      'local' => [
        'magento' => 'pb_uat',
        'conductor' => 'pbkw_dev2',
      ],
      'dev2' => [
        'magento' => 'pb_uat',
        'conductor' => 'pbkw_dev2',
      ],
    ],
    'pbae' => [
      'dev' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbae_dev',
      ],
      'dev2' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbae_dev2',
      ],
    ],
    'vsae' => [
      'dev' => [
        'magento' => 'vs_qa',
        'conductor' => 'vsae_dev',
      ],
      'local'=> [
        'magento' => 'vs_uat',
        'conductor' => 'vsae_dev2',
      ],
      'dev2' => [
        'magento' => 'vs_uat',
        'conductor' => 'vsae_dev2',
      ],
    ],
    'aeokw' => [
      'dev' => [
        'magento' => 'aeo_qa',
        'conductor' => 'aeokw_dev',
      ],
    ],
  ];

  // All 01update should match 01live.
  // Update array to set 01update if 01live is set.
  foreach ($mapping as $key => $value) {
    if (isset($mapping[$key]['live'])) {
      $mapping[$key]['update'] = $mapping[$key]['live'];
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
