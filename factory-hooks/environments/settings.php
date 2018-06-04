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
        '01live' => [
          'alshaya_acm_knet.settings' => [
            'alias' => 'hm',
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
