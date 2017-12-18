<?php

/**
 * @file
 * Provides Drush commands for Acquia Commerce Middleware related activities.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_api_drush_command() {
  $commands = [];

  $commands['alshaya-api-sync-stores'] = [
    'description' => 'Imports all stores into Drupal using direct Magento API.',
    'aliases'     => ['aass'],
  ];

  $commands['alshaya-api-sanity-check'] = [
    'description' => 'Run sanity check to validate SKU with Magento',
    'aliases'     => ['aasc'],
    'options' => [
      'types' => 'The comma-separated list of SKUs types to check (simple, configurable)',
    ],
  ];

  return $commands;
}

/**
 * Implements drush sanity check command.
 */
function drush_alshaya_api_sanity_check() {
  $types = array_map('trim', explode(',', drush_get_option('types', 'simple,configurable')));

  $verbose = drush_get_option('verbose', FALSE);
  $languages = \Drupal::languageManager()->getLanguages();

  drush_print(dt('Getting @types SKUs from Magento, please wait...', [
    '@types' => implode(dt(' and '), $types),
  ]));

  // Retrieve all enabled SKUs from Magento indexed by type.
  $mskus = \Drupal::service('alshaya_api.api')->getSkus($types);

  if ($verbose) {
    foreach ($types as $type) {
      drush_print(dt("@type SKUs (@count) from Magento:\n@skus"), [
        '@type' => $type,
        '@count' => count($mskus[$type]),
        '@skus' => "'" . implode("','", $mskus[$type]) . "'",
      ]);
    }
  }

  drush_print(dt("\nGetting @types SKUs from Drupal, please wait...", [
    '@types' => implode(dt(' and '), $types),
  ]));

  // Get all SKUs from Drupal indexed by type and langcode.
  foreach ($types as $type) {
    foreach ($languages as $language) {
      $dskus[$type][$language->getId()] = \Drupal::service('alshaya_acm_product.skumanager')->getSkus($language->getId(), $type);

      if ($verbose) {
        drush_print(dt("@type @language SKUs (@count) from Drupal:\n@skus"), [
          '@type' => $type,
          '@language' => $language->getName(),
          '@count' => count($dskus[$type][$language->getId()]),
          '@skus' => "'" . implode("','", $dskus[$type][$language->getId()]) . "'",
        ]);
      }
    }
  }

  drush_print(dt("\n#### SUMMARY ####"));

  $missing = [];
  $to_be_deleted = [];

  foreach ($types as $type) {
    $missing[$type]['all'] = [];
    $to_be_deleted[$type]['all'] = [];

    foreach ($languages as $language) {
      // The ones which are missing in Drupal.
      $missing[$type][$language->getId()] = array_diff($mskus[$type], $dskus[$type][$language->getId()]);
      $mall = array_merge($missing[$type]['all'], $missing[$type][$language->getId()]);
      $missing[$type]['all'] = $mall;

      // The ones which are only in Drupal and should be removed.
      $to_be_deleted[$type][$language->getId()] = array_diff($dskus[$type][$language->getId()], $mskus[$type]);
      $tall = array_merge($to_be_deleted[$type]['all'], $to_be_deleted[$type][$language->getId()]);
      $to_be_deleted[$type]['all'] = $tall;

      if (!empty($missing[$type][$language->getId()])) {
        drush_print(dt("Following @count @language @type's SKUs are missing in Drupal and must be synced:\n!skus", [
          '@count' => count($missing[$type][$language->getId()]),
          '@language' => $language->getName(),
          '@type' => $type,
          '!skus' => "'" . implode("','", $missing[$type][$language->getId()]) . "'",
        ]));
      }
      else {
        drush_print(dt('No missing SKUs match for @language @type in Drupal.', [
          '@language' => $language->getName(),
          '@type' => $type,
        ]));
      }

      if (!empty($to_be_deleted[$type][$language->getId()])) {
        drush_print(dt("Following @count @language @type's SKUs are only in Drupal and must be removed:\n!skus", [
          '@count' => count($to_be_deleted[$type][$language->getId()]),
          '@language' => $language->getName(),
          '@type' => $type,
          '!skus' => "'" . implode("','", $to_be_deleted[$type][$language->getId()]) . "'",
        ]));
      }
      else {
        drush_print(dt('No additional SKUs for @language @type found in Drupal. Nothing to delete.', [
          '@language' => $language->getName(),
          '@type' => $type,
        ]));
      }
    }

    $missing[$type]['all'] = array_unique($missing[$type]['all']);
    $to_be_deleted[$type]['all'] = array_unique($to_be_deleted[$type]['all']);
  }

  drush_print(dt("\n#### SYNCHRONIZATION ####"));

  // Retrieve missing SKUs.
  foreach ($types as $type) {
    if (!empty($missing[$type]['all']) && drush_confirm(dt('Do you want to launch ProductSync on following !count !type missing SKUs: !skus', [
      '!count' => count($missing[$type]['all']),
      '!type' => $type,
      '!skus' => "'" . implode("','", $missing[$type]['all']) . "'",
    ]))) {
      foreach (acq_commerce_get_store_language_mapping() as $langcode => $store_id) {
        // We split the list of SKUs in small chunk to avoid any issue. This is
        // only to send the request to Conductor.
        foreach (array_chunk(str_replace("'", '', $missing[$type]['all']), 5) as $chunk) {
          // @TODO: Make page size a config. It can be used in multiple places.
          \Drupal::service('acq_commerce.ingest_api')->productFullSync($store_id, $langcode, $chunk, 2);
        }

        drush_print(dt('ProductSync launched for @count @language @type SKUs.', [
          '@count' => count($missing[$type]['all']),
          '@language' => $languages[$langcode]->getName(),
          '@type' => $type,
        ]));
      }
    }
  }

  // Delete additional SKUs.
  foreach ($types as $type) {
    if (!empty($to_be_deleted[$type]['all']) && drush_confirm(dt('Do you want to delete following !count !type additional SKUs: !skus', [
      '!count' => count($to_be_deleted[$type]['all']),
      '!type' => $type,
      '!skus' => "'" . implode("','", $to_be_deleted[$type]['all']) . "'",
    ]))) {
      // @TODO: Investigate potential issue if SKUs is created in Magento right
      // after we get the list via API. It may remove newly created SKUs.
      // $sku_manager = \Drupal::service('entity_type.manager')->getStorage('acq_sku');
      drush_print(dt('TODO: Delete additional !type', ['!type' => $type]));
    }
  }
}

/**
 * Implements drush store sync command.
 */
function drush_alshaya_api_sync_stores() {
  drush_print(dt('Synchronizing all stores, please wait...'));

  /** @var \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper */
  $api_wrapper = \Drupal::service('alshaya_api.api');

  if ($api_wrapper->syncStores()) {
    \Drupal::logger('alshaya_api')->notice('Stores sync completed.');
  }
  else {
    \Drupal::logger('alshaya_api')->error('Either no stores found or error occurred while doing stores sync. Please check logs for more details.');
  }
}
