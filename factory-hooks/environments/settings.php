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
        ]
      ]
    ],
    'hmkw' => [
      'default' => [
        'store_id' => [
          'ar' => 2,
        ]
      ]
    ],
    'default' => [
      'default' => [
        'store_id' => [
          'en' => 1,
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
