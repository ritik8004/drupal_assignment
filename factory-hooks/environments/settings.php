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
function alshaya_get_additional_settings($site_code, $country_code, $env) {
  $mapping = [
    'mc' => [
      'default' => [
        'default' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-PP5PK4C',
          ],
        ],
      ],
      'kw' => [
        '01uat' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-PP5PK4C',
          ],
        ],
      ],
      'ae' => [
        '01uat' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-TTQFBZ',
          ],
        ],
      ],
      'sa' => [
        '01uat' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-NMZXSP',
          ],
        ],
      ],
    ],
    'hm' => [
      'kw' => [
        '01live' => [
          'alshaya_acm_knet.settings' => [
            'alias' => 'hm',
          ],
        ],
        '01uat' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-NQ4JXJP',
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
      'ae' => [
        '01uat' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-5ZNGJRP',
          ],
        ],
      ],
      'sa' => [
        '01uat' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-PXGWK9J',
          ],
        ],
      ],
    ],
    'bbw' => [
      'default' => [
        'default' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-KXQ8P3S',
          ],
        ],
      ],
      'ae' => [
        '01uat' => [
          'google_tag.settings' => [
            'container_id' => 'GTM-N6X25H2',
          ],
        ],
      ],
    ],
    'default' => [
      'default' => [
        'default' => [
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
  if (isset($mapping['default'][$country_code]['default'])) {
    $settings = array_replace_recursive($settings, $mapping['default'][$country_code]['default']);
  }
  if (isset($mapping[$site_code]['default']['default'])) {
    $settings = array_replace_recursive($settings, $mapping[$site_code]['default']['default']);
  }
  if (isset($mapping[$site_code][$country_code]['default'])) {
    $settings = array_replace_recursive($settings, $mapping[$site_code][$country_code]['default']);
  }
  if (isset($mapping[$site_code][$country_code][$env])) {
    $settings = array_replace_recursive($settings, $mapping[$site_code][$country_code][$env]);
  }

  return $settings;
}
