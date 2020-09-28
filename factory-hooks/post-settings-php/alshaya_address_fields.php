<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

/**
 * Setting contains the required address fields of extension attributes.
 *
 * This is used for now to validate address custom attributes before order
 * placement so that order has all required address data.
 *
 * By default 'default' key will be used if not specified or override
 * and brand/country specific setting.
 *
 * If need to override brand specific, then pls do below in code or on server
 * home directory in brand specific file.
 *  - $settings['alshaya_address_fields']['hm']['kw'] = ['field1', 'field2']
 */
$settings['alshaya_address_fields'] = [
  'default' => [
    'kw' => [
      'governate',
    ],
  ],
];
