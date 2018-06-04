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
  // Like mc, hm or pb.
  $site_name = substr($site, 0, -2);
  // Like kw, sa or ae.
  $country = substr($site, -2, 2);

  $mapping = [
    'mc' => [
      'kw' => [
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
        ],
      ],
      'sa' => [
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
        '01live' => [
          'store_id' => [
            'en' => 7,
            'ar' => 13,
          ],
        ],
      ],
      'ae' => [
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
        '01live' => [
          'store_id' => [
            'en' => 19,
            'ar' => 22,
          ],
        ],
      ],
      'default' => [
        'default' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-PP5PK4C',
          ],
        ],
      ],
    ],
    'hm' => [
      'kw' => [
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
        ],
      ],
      'sa' => [
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
        '01live' => [
          'store_id' => [
            'en' => 8,
            'ar' => 14,
          ],
        ],
      ],
      'ae' => [
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
        '01live' => [
          'store_id' => [
            'en' => 17,
            'ar' => 23,
          ],
        ],
      ],
      'default' => [
        'default' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-NQ4JXJP',
          ],
        ],
      ],
    ],
    'bbw' => [
      'kw' => [
        'default' => [
          'store_id' => [
            'en' => 1,
            'ar' => 2,
          ],
          'magento_lang_prefix' => [
            'en' => 'kwt_en',
            'ar' => 'kwt_ar',
          ],
        ],
        '01uat' => [
          'store_id' => [
            'en' => 1,
            'ar' => 19,
          ],
        ],
      ],
      'sa' => [
        'default' => [
          'store_id' => [
            'en' => 3,
            'ar' => 4,
          ],
          'magento_lang_prefix' => [
            'en' => 'sau_en',
            'ar' => 'sau_ar',
          ],
        ],
        '01uat' => [
          'store_id' => [
            'en' => 7,
            'ar' => 10,
          ],
        ],
      ],
      'ae' => [
        'default' => [
          'store_id' => [
            'en' => 5,
            'ar' => 6,
          ],
          'magento_lang_prefix' => [
            'en' => 'are_en',
            'ar' => 'are_ar',
          ],
        ],
        '01uat' => [
          'store_id' => [
            'en' => 13,
            'ar' => 16,
          ],
        ],
      ],
    ],
    'pb' => [
      'ae' => [
        //'default' => [
        //  'store_id' => [
        //    'en' => 1,
        //    'ar' => 2,
        //  ],
        //  'magento_lang_prefix' => [
        //    'en' => 'uae_en',
        //    'ar' => 'uae_ar',
        //  ],
        //],
        // PBAE is connected to MCAE UAT for now.
        'default' => [
          'store_id' => [
            'en' => 16,
            'ar' => 13,
          ],
          'magento_lang_prefix' => [
            'en' => 'uae_en',
            'ar' => 'uae_ar',
          ],
        ],
      ],
    ],
    'vs' => [
      'ae' => [
        // VSAE is connected to MCAE UAT for now.
        'default' => [
          'store_id' => [
            'en' => 16,
            'ar' => 13,
          ],
          'magento_lang_prefix' => [
            'en' => 'uae_en',
            'ar' => 'uae_ar',
          ],
        ],
      ],
    ],
    'default' => [
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
          'google_tag.settings' => [
            'container_id' => '',
          ],
        ],
      ],
    ],
  ];

  // Get the settings following this fallback (from the more generic to the
  // more specific one): default+default+default > site+country+env.
  $settings = [];

  if (isset($mapping['default']['default']['default'])) {
    $settings = array_replace_recursive($settings, $mapping['default']['default']['default']);
  }
  if (isset($mapping['default']['default'][$env])) {
    $settings = array_replace_recursive($settings, $mapping['default']['default'][$env]);
  }
  if (isset($mapping['default'][$country]['default'])) {
    $settings = array_replace_recursive($settings, $mapping['default'][$country]['default']);
  }
  if (isset($mapping[$site_name]['default']['default'])) {
    $settings = array_replace_recursive($settings, $mapping[$site_name]['default']['default']);
  }
  if (isset($mapping[$site_name][$country]['default'])) {
    $settings = array_replace_recursive($settings, $mapping[$site_name][$country]['default']);
  }
  if (isset($mapping[$site_name][$country][$env])) {
    $settings = array_replace_recursive($settings, $mapping[$site_name][$country][$env]);
  }

  return $settings;
}
