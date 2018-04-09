<?php
/**
 * @file.
 *
 * This file contains some settings which are environment and/or site
 * dependent. The function will identify the appropriate settings to apply
 * which will be then merged with global settings.
 */

/**
 * Get settings which are environment and/or site dependent.
 */
function alshaya_get_additional_settings($site, $env) {
  $mapping = [
    'mckw' => [
      '01live' => [
        'store_id' => [
          'ar' => 4,
        ],
      ],
      '01update' => [
        'store_id' => [
          'ar' => 4,
        ],
      ],
      'default' => [
        'store_id' => [
          'ar' => 3,
        ],
      ]
    ],
    'mcsa' => [
      'default' => [
        'store_id' => [
          'en' => 5,
          'ar' => 4,
        ],
        'magento_lang_prefix' => [
          'en' => 'ksa_en',
          'ar' => 'ksa_ar',
        ],
      ],
      '01uat' => [
        'store_id' => [
          'en' => 7,
          'ar' => 10,
        ],
      ],
    ],
    'mcae' => [
      'default' => [
        'store_id' => [
          'en' => 7,
          'ar' => 6,
        ],
        'magento_lang_prefix' => [
          'en' => 'uae_en',
          'ar' => 'uae_ar',
        ],
      ],
      '01uat' => [
        'store_id' => [
          'en' => 16,
          'ar' => 13,
        ],
      ],
    ],
    'hmkw' => [
      'default' => [
        'store_id' => [
          'ar' => 2,
        ],
        'magento_lang_prefix' => [
          'en' => 'default',
        ],
      ],
      '01live' => [
        'store_id' => [
          'ar' => 5,
        ],
        'alshaya_acm_knet.settings' => [
          'alias' => 'hm',
        ],
      ]
    ],
    'hmsa' => [
      'default' => [
        'store_id' => [
          'en' => 3,
          'ar' => 4,
        ],
        'magento_lang_prefix' => [
          'en' => 'ksa_en',
          'ar' => 'ksa_ar',
        ],
      ],
      '01uat' => [
        'store_id' => [
          'en' => 8,
          'ar' => 5,
        ],
      ],
    ],
    'hmae' => [
      'default' => [
        'store_id' => [
          'en' => 6,
          'ar' => 5,
        ],
        'magento_lang_prefix' => [
          'en' => 'uae_en',
          'ar' => 'uae_ar',
        ],
      ],
      '01uat' => [
        'store_id' => [
          'en' => 14,
          'ar' => 11,
        ],
      ],
    ],
    'pbae' => [
      'default' => [
        'en' => 1,
        'ar' => 1,
      ],
      'magento_lang_prefix' => [
        'en' => 'uae_en',
        'ar' => 'uae_en',
      ],
    ],
    'default' => [
      'default' => [
        'store_id' => [
          'en' => 1,
          'ar' => 2,
        ],
        'magento_lang_prefix' => [
          'en' => 'kwt_en',
          'ar' => 'kwt_ar',
        ],
        'alshaya_acm_knet.settings' => [
          'alias' => 'alshaya',
        ],
      ],
    ],
  ];

  // Get the settings following this fallback (from the more generic to the
  // more specific one): default+default > default+env > site+default >
  // site+env.
  $settings = [];
  if (isset($mapping['default']['default'])) {
    $settings = array_replace_recursive($settings, $mapping['default']['default']);
  }
  if (isset($mapping['default'][$env])) {
    $settings = array_replace_recursive($settings, $mapping['default'][$env]);
  }
  if (isset($mapping[$site]['default'])) {
    $settings = array_replace_recursive($settings, $mapping[$site]['default']);
  }
  if (isset($mapping[$site][$env])) {
    $settings = array_replace_recursive($settings, $mapping[$site][$env]);
  }

  return $settings;
}
