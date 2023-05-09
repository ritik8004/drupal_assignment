<?php
// phpcs:ignoreFile

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
    // Adding magento host to be used for reset config.
    $settings['alshaya_api.settings']['magento_host'] = $magentos[$env_keys['magento']]['url'];
    // Use the Magento ENV key by default but allow overriding it.
    // @todo make it a configuration instead of Setting.
    $settings['algolia_env'] = $magentos[$env_keys['magento']]['algolia_env'] ?? $env_keys['magento'];
    // Adding magento secrets to be used for reset config.
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
        'magento' => 'hm_test',
        'conductor' => 'hmkw_dev',
      ],
      'test' => [
        'magento' => 'hm_test',
        'conductor' => 'hmkw_qa',
      ],
      'qa2' => [
        'magento' => 'hm_test',
        'conductor' => 'hmkw_qa2',
      ],
    ],
    'hmsa' => [
      'qa2' => [
        'magento' => 'hm_test',
        'conductor' => 'hmsa_qa2',
      ],
      'dev' => [
        'magento' => 'hm_test',
        'conductor' => 'hmsa_dev',
      ],
      'test' => [
        'magento' => 'hm_test',
        'conductor' => 'hmsa_qa',
      ],
      'dev2' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmsa_dev2',
      ],
    ],
    'hmae' => [
      'dev' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmae_dev',
      ],
      'test' => [
        'magento' => 'hm_test',
        'conductor' => 'hmae_qa',
      ],
      'dev2' => [
        'magento' => 'hm_qa',
        'conductor' => 'hmae_dev2',
      ],
    ],
    'hmeg' => [
      'test' => [
        'magento' => 'hm_test',
        'conductor' => 'hmeg_qa',
      ],
    ],
    'hmqa' => [
      'qa2' => [
        'magento' => 'hm_test',
        'conductor' => 'hmqa_qa2',
      ],
      'dev3' => [
        'magento' => 'hm_apc',
        'conductor' => 'hmqa_dev3',
      ],
    ],
    'flkw' => [
      'dev' => [
        'magento' => 'fl_qa',
        'conductor' => 'flkw_dev',
      ],
    ],
    'flsa' => [
      'dev' => [
        'magento' => 'fl_qa',
        'conductor' => 'flsa_dev',
      ],
    ],
    'flae' => [
      'dev' => [
        'magento' => 'fl_qa',
        'conductor' => 'flae_dev',
      ],
    ],
    'bbwkw' => [
      'dev3' => [
        'magento' => 'bbw_apc',
        'conductor' => 'bbwkw_dev3',
      ],
    ],
    'bbwae' => [
      'dev' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwae_dev',
      ],
      'dev2' => [
        'magento' => 'bbw_uat',
        'conductor' => 'bbwae_dev2',
      ],
      'dev3' => [
        'magento' => 'bbw_apc',
        'conductor' => 'bbwae_dev3',
      ],
      'qa2' => [
        'magento' => 'bbw_oms_sit',
        'conductor' => 'bbwae_sit_dev2',
      ],
    ],
    'bbwsa' => [
      'dev2' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwsa_dev2',
      ],
    ],
    'bbwbh' => [
      'dev' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwbh_dev',
      ],
    ],
    'bbwqa' => [
      'dev' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwqa_dev',
      ],
      'dev3' => [
        'magento' => 'bbw_uat',
        'conductor' => 'bbwqa_dev3',
      ],
    ],
    'bbwjo' => [
      'dev' => [
        'magento' => 'bbw_qa',
        'conductor' => 'bbwjo_dev',
      ],
    ],
    'mckw' => [
      'dev' => [
        'magento' => 'mc_qa',
        'conductor' => 'mckw_dev',
      ],
    ],
    'mcae' => [
      'dev2' => [
        'magento' => 'mc_uat',
        'conductor' => 'mcae_dev2',
      ],
    ],
    'mcsa' => [
      'dev' => [
        'magento' => 'mc_qa',
        'conductor' => 'mcsa_dev',
      ],
    ],
    'mcqa' => [
      'dev3' => [
        'magento' => 'mc_apc',
        'conductor' => 'mcqa_dev3',
      ],
    ],
    'mceg' => [
      'dev3' => [
        'magento' => 'mc_apc',
        'conductor' => 'mceg_dev3',
      ],
    ],
    'mcbh' => [
      'dev3' => [
        'magento' => 'mc_apc',
        'conductor' => 'mcbh_dev3',
      ],
    ],
    'pbkw' => [
      'qa2' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbkw_qa',
      ],
      'dev2' => [
        'magento' => 'pb_uat',
        'conductor' => 'pbkw_dev2',
      ],
      'dev3' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbkw_dev3',
      ],
    ],
    'pbae' => [
      'qa2' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbae_qa',
      ],
      'dev' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbae_qa',
      ],
      'dev2' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbae_dev2',
      ],
    ],
    'pbsa' => [
      'qa2' => [
        'magento' => 'pb_qa',
        'conductor' => 'pbsa_qa',
      ],
    ],
    'vskw' => [
      'dev2' => [
        'magento' => 'vs_qa',
        'conductor' => 'vskw_dev2',
      ],
    ],
    'vssa' => [
      'dev2' => [
        'magento' => 'vs_qa',
        'conductor' => 'vssa_dev2',
      ],
    ],
    'vsae' => [
      'qa2' => [
        'magento' => 'vs_qa',
        'conductor' => 'vsae_qa',
      ],
      'dev' => [
        'magento' => 'vs_qa',
        'conductor' => 'vsae_dev',
      ],
      'dev2' => [
        'magento' => 'vs_qa',
        'conductor' => 'vsae_dev2',
      ],
    ],
    'vsqa' => [
      'dev3' => [
        'magento' => 'vs_apc',
        'conductor' => 'vsqa_dev3',
      ],
    ],
    'vsxb' => [
      'dev3' => [
        'magento' => 'vs_integration',
        'conductor' => 'vsxb_dev3',
      ],
      'dev2' => [
        'magento' => 'vs_integration',
        'conductor' => 'vsxb_dev2',
      ],
      'test' => [
        'magento' => 'vs_test',
        'conductor' => 'vsxb_test',
      ],
      'local' => [
        'magento' => 'vs_integration',
        'conductor' => 'vsxb_dev3',
      ],
      'pprod' => [
        'magento' => 'vs_pprod',
        'conductor' => 'vsxb_pprod',
      ],
    ],
    'wekw' => [
      'dev2' => [
        'magento' => 'we_pprod',
        'conductor' => 'wekw_dev2',
      ],
      'qa2' => [
        'magento' => 'we_qa',
        'conductor' => 'wekw_test',
      ],
      'dev' => [
        'magento' => 'we_qa',
        'conductor' => 'wekw_dev',
      ],
    ],
    'wesa' => [
      'dev2' => [
        'magento' => 'we_pprod',
        'conductor' => 'wesa_dev2',
      ],
      'qa2' => [
        'magento' => 'we_qa',
        'conductor' => 'wesa_test',
      ],
    ],
    'weae' => [
      'dev2' => [
        'magento' => 'we_pprod',
        'conductor' => 'weae_dev2',
      ],
      'qa2' => [
        'magento' => 'we_qa',
        'conductor' => 'weae_test',
      ],
    ],
    'aeoxb' => [
      'dev3' => [
        'magento' => 'aeo_apc',
        'conductor' => 'aeoxb_dev3',
      ],
      'test' => [
        'magento' => 'aeo_test',
        'conductor' => 'aeoxb_test',
      ],
      'dev' => [
        'magento' => 'aeo_apc',
        'conductor' => 'aeoxb_dev3',
      ],
      'local' => [
        'magento' => 'aeo_apc',
        'conductor' => 'aeoxb_dev3',
      ],
      'pprod' => [
        'magento' => 'aeo_pprod',
        'conductor' => 'aeoxb_pprod',
      ],
    ],
    'aeokw' => [
      'dev2' => [
        'magento' => 'aeo_pprod',
        'conductor' => 'aeokw_dev2',
      ],
    ],
    'aeosa' => [
      'dev2' => [
        'magento' => 'aeo_pprod',
        'conductor' => 'aeosa_dev2',
      ],
    ],
    'aeoqa' => [
      'dev3' => [
        'magento' => 'aeo_apc',
        'conductor' => 'aeoqa_dev3',
      ],
    ],
    'aeoae' => [
      'dev2' => [
        'magento' => 'aeo_pprod',
        'conductor' => 'aeoae_dev2',
      ],
    ],
    'aeoeg' => [
      'dev2' => [
        'magento' => 'aeo_pprod',
        'conductor' => 'aeoeg_dev2',
      ],
    ],
    'aeoeg' => [
      'dev3' => [
        'magento' => 'aeo_apc',
        'conductor' => 'aeoeg_dev3',
      ],
    ],
    'aeobh' => [
      'dev3' => [
        'magento' => 'aeo_apc',
        'conductor' => 'aeobh_dev3',
      ],
    ],
    'bpkw' => [
      'qa2' => [
        'magento' => 'bp_test',
        'conductor' => 'bpkw_qa',
      ],
      'dev' => [
        'magento' => 'bp_qa',
        'conductor' => 'bpkw_dev',
      ],
    ],
    'bpsa' => [
      'dev' => [
        'magento' => 'bp_qa',
        'conductor' => 'bpsa_dev',
      ],
    ],
    'bpae' => [
      'dev' => [
        'magento' => 'bp_qa',
        'conductor' => 'bpae_dev',
      ],
      'qa2' => [
        'magento' => 'bp_test',
        'conductor' => 'bpae_qa2',
      ],
    ],
    'bpsa' => [
      'dev' => [
        'magento' => 'bp_qa',
        'conductor' => 'bpsa_dev',
      ],
    ],
    'bpeg' => [
      'dev' => [
        'magento' => 'bp_qa',
        'conductor' => 'bpeg_dev',
      ],
    ],
    'bpeg' => [
      'dev' => [
        'magento' => 'bp_qa',
        'conductor' => 'bpeg_dev',
      ],
    ],
    'bpqa' => [
      'dev3' => [
        'magento' => 'bp_apc',
        'conductor' => 'bpqa_dev3',
      ],
    ],
    'muae' => [
      'dev' => [
        'magento' => 'mu_qa',
        'conductor' => 'muae_dev',
      ],
    ],
    'muqa' => [
      'dev3' => [
        'magento' => 'mu_apc',
        'conductor' => 'muqa_dev3',
      ],
    ],
    'tbsae' => [
      'qa2' => [
        'magento' => 'tbs_test',
        'conductor' => 'tbsae_test',
      ],
      'dev' => [
        'magento' => 'tbs_qa',
        'conductor' => 'tbsae_dev',
      ],
    ],
    'tbskw' => [
      'qa2' => [
        'magento' => 'tbs_test',
        'conductor' => 'tbskw_test',
      ],
      'dev' => [
        'magento' => 'tbs_qa',
        'conductor' => 'tbskw_dev',
      ],
    ],
    'tbsbh' => [
      'qa2' => [
        'magento' => 'tbs_test',
        'conductor' => 'tbsbh_test',
      ],
    ],
    'tbseg' => [
      'qa2' => [
        'magento' => 'tbs_test',
        'conductor' => 'tbseg_test',
      ],
    ],
    'tbsqa' => [
      'qa2' => [
        'magento' => 'tbs_test',
        'conductor' => 'tbsqa_test',
      ],
    ],
    'coskw' => [
      'qa2' => [
        'magento' => 'cos_qa',
        'conductor' => 'coskw_qa2',
      ],
      'dev' => [
        'magento' => 'cos_qa',
        'conductor' => 'coskw_dev',
      ],
      'uat' => [
        'magento' => 'cos_uat',
        'conductor' => 'coskw_uat',
      ],
    ],
    'cossa' => [
      'qa2' => [
        'magento' => 'cos_qa',
        'conductor' => 'cossa_qa2',
      ],
      'dev' => [
        'magento' => 'cos_qa',
        'conductor' => 'cossa_dev',
      ],
      'uat' => [
        'magento' => 'cos_uat',
        'conductor' => 'cossa_uat',
      ],
    ],
    'cosae' => [
      'qa2' => [
        'magento' => 'cos_qa',
        'conductor' => 'cosae_qa2',
      ],
      'dev' => [
        'magento' => 'cos_qa',
        'conductor' => 'cosae_dev',
      ],
      'uat' => [
        'magento' => 'cos_uat',
        'conductor' => 'cosae_uat',
      ],
    ],
    'coseg' => [
      'qa2' => [
        'magento' => 'cos_qa',
        'conductor' => 'coseg_qa2',
      ],
      'dev' => [
        'magento' => 'cos_qa',
        'conductor' => 'coseg_dev',
      ],
      'uat' => [
        'magento' => 'cos_uat',
        'conductor' => 'coseg_uat',
      ],
    ],
    'cosbh' => [
      'qa2' => [
        'magento' => 'cos_qa',
        'conductor' => 'cosbh_qa2',
      ],
      'dev' => [
        'magento' => 'cos_qa',
        'conductor' => 'cosbh_dev',
      ],
      'uat' => [
        'magento' => 'cos_uat',
        'conductor' => 'cosbh_uat',
      ],
    ],
    'cosqa' => [
      'qa2' => [
        'magento' => 'cos_qa',
        'conductor' => 'cosqa_qa2',
      ],
      'dev' => [
        'magento' => 'cos_qa',
        'conductor' => 'cosqa_dev',
      ],
      'uat' => [
        'magento' => 'cos_uat',
        'conductor' => 'cosqa_uat',
      ],
    ],
    'pbkae' => [
      'qa2' => [
        'magento' => 'pbk_qa',
        'conductor' => 'pbkae_test',
      ],
    ],
    'pbksa' => [
      'qa2' => [
        'magento' => 'pbk_qa',
        'conductor' => 'pbksa_test',
      ],
    ],
    'pbkkw' => [
      'qa2' => [
        'magento' => 'pbk_qa',
        'conductor' => 'pbkkw_test',
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
