<?php

/**
 * @file
 * Provides Drush commands for Acquia Commerce Middleware related activities.
 */

use Drupal\acq_sku\Entity\SKU;

/**
 * Implements hook_drush_command().
 */
function alshaya_api_drush_command() {
  $commands = [];

  $commands['alshaya-api-sanity-check-sku-diff'] = [
    'description' => 'Run sanity check to get a diff of SKUs between Drupal and Magento.',
    'aliases' => ['aascsd'],
    'options' => [
      'types' => 'The comma-separated list of SKUs types to check (simple, configurable)',
      'magento_source' => 'The source to get the SKUs (api, report). Default is merchandising report.',
      'page_size' => 'ACM page size',
      'use_delete' => 'Hidden deletion option',
    ],
  ];

  $commands['alshaya-api-sanity-check-visibility'] = [
    'description' => 'Run sanity check to identify products from SKUs which are not supposed to be visible.',
    'aliases' => ['aascv'],
  ];

  return $commands;
}

/**
 * Implements drush sanity check sku diff command.
 */
function drush_alshaya_api_sanity_check_sku_diff() {
  $types = array_map('trim', explode(',', drush_get_option('types', 'simple,configurable')));

  $msource = drush_get_option('magento_source', 'report');
  $debug = drush_get_option('debug', FALSE);
  $verbose = drush_get_option('verbose', FALSE);
  $languages = \Drupal::languageManager()->getLanguages();

  $page_size = drush_get_option('page_size', 10);
  $use_delete = drush_get_option('use_delete', FALSE);

  drush_print(dt('Getting @types SKUs from Magento, please wait...', [
    '@types' => implode(dt(' and '), $types),
  ]));

  // Retrieve all enabled SKUs from Magento indexed by type.
  if ($msource == 'report') {
    $mskus = \Drupal::service('alshaya_api.api')->getEnabledSkusFromMerchandisingReport($types);
  }
  else {
    $mskus = \Drupal::service('alshaya_api.api')->getSkusFromApi($types);
  }

  if ($debug) {
    foreach ($types as $type) {
      drush_print(dt("@type SKUs (@count) from Magento:\n@skus", [
        '@type' => $type,
        '@count' => count($mskus[$type]),
        '@skus' => "'" . implode("','", array_keys($mskus[$type])) . "'",
      ]));
    }

    // Notify in debug mode.
    if ($msource == 'api') {
      drush_print((dt('With source=api, stock and price will not be validated for skus.')));
    }
  }

  drush_print(dt("\nGetting @types SKUs from Drupal, please wait...", [
    '@types' => implode(dt(' and '), $types),
  ]));

  // Get all SKUs from Drupal indexed by type and langcode.
  foreach ($types as $type) {
    foreach ($languages as $language) {
      $dskus[$type][$language->getId()] = \Drupal::service('alshaya_acm_product.skumanager')->getSkus($language->getId(), $type);

      if ($debug) {
        drush_print(dt("@type @language SKUs (@count) from Drupal:\n@skus", [
          '@type' => $type,
          '@language' => $language->getName(),
          '@count' => count($dskus[$type][$language->getId()]),
          '@skus' => "'" . implode("','", array_keys($dskus[$type][$language->getId()])) . "'",
        ]));
      }
    }
  }

  drush_print(dt("\n#### SUMMARY ####"));

  $missing = [];
  $to_be_deleted = [];
  $stock_price_mismatch_sync = [];

  foreach ($types as $type) {
    $missing[$type]['all'] = [];
    $to_be_deleted[$type]['all'] = [];
    $stock_price_mismatch[$type] = [];

    foreach ($languages as $language) {
      // The ones which are missing in Drupal.
      $missing[$type][$language->getId()] = array_diff(array_keys($mskus[$type]), array_keys($dskus[$type][$language->getId()]));
      $mall = array_merge($missing[$type]['all'], $missing[$type][$language->getId()]);
      $missing[$type]['all'] = $mall;

      // The ones which are only in Drupal and should be removed.
      $to_be_deleted[$type][$language->getId()] = array_diff(array_keys($dskus[$type][$language->getId()]), array_keys($mskus[$type]));
      $tall = array_merge($to_be_deleted[$type]['all'], $to_be_deleted[$type][$language->getId()]);
      $to_be_deleted[$type]['all'] = $tall;

      // Stock and price check only for simple SKUs with merchandiser report
      // as api doesn't provide the data/info required.
      if ($type == 'simple' && $msource == 'report') {
        // The ones which have different stock/price in Drupal and Magento.
        foreach ($dskus[$type][$language->getId()] as $key => $data) {
          if (!empty($mskus[$type][$key])) {
            $output = '';
            // If stock in drupal not matches with what in magento.
            if ($data['stock'] != $mskus[$type][$key]['qty']) {
              $output .= 'Drupal stock:' . $data['stock'] . ' | ';
              $output .= 'MDC stock:' . $mskus[$type][$key]['qty'];
              $stock_price_mismatch_sync[$type][] = $key;
            }

            // If price in drupal not matches with what in magento.
            if ($data['price'] != $mskus[$type][$key]['price']) {
              $output .= 'Drupal price:' . $data['price'] . ' | ';
              $output .= 'MDC price:' . $mskus[$type][$key]['price'];
              $stock_price_mismatch_sync[$type][] = $key;
            }

            // If special price in drupal not matches with what in magento.
            if ($data['special_price'] != $mskus[$type][$key]['special_price']) {
              $output .= 'Drupal spl price:' . $data['special_price'] . ' | ';
              $output .= 'MDC spl price:' . $mskus[$type][$key]['special_price'];
              $stock_price_mismatch_sync[$type][] = $key;
            }

            if (!empty($output)) {
              $stock_price_mismatch[$type][$language->getId()][$key] = "SKU:" . $key . " | " . $output;
            }
          }
        }

        // Output the details of stock/price mismatch.
        if (!empty($stock_price_mismatch[$type][$language->getId()])) {
          drush_print(dt("\n@count @language @type's SKUs in drupal have different stock/price than magento:\n!output", [
            '@count' => count($stock_price_mismatch[$type][$language->getId()]),
            '@language' => $language->getName(),
            '@type' => $type,
            '!output' => $verbose ? implode("\n", $stock_price_mismatch[$type][$language->getId()]) : '',
          ]));
        }
        else {
          drush_print(dt("\nNo stock/price mismatch for @language @type's in Drupal.", [
            '@language' => $language->getName(),
            '@type' => $type,
          ]));
        }
      }

      if (!empty($missing[$type][$language->getId()])) {
        drush_print(dt("\n@count @language @type's SKUs are missing in Drupal and must be synced:\n!skus", [
          '@count' => count($missing[$type][$language->getId()]),
          '@language' => $language->getName(),
          '@type' => $type,
          '!skus' => $verbose ? "'" . implode("','", $missing[$type][$language->getId()]) . "'" : '',
        ]));
      }
      else {
        drush_print(dt("\nNo missing SKUs match for @language @type in Drupal.", [
          '@language' => $language->getName(),
          '@type' => $type,
        ]));
      }

      if (!empty($to_be_deleted[$type][$language->getId()])) {
        drush_print(dt("\n@count @language @type's SKUs are only in Drupal and must be removed:\n!skus", [
          '@count' => count($to_be_deleted[$type][$language->getId()]),
          '@language' => $language->getName(),
          '@type' => $type,
          '!skus' => $verbose ? "'" . implode("','", $to_be_deleted[$type][$language->getId()]) . "'" : '',
        ]));
      }
      else {
        drush_print(dt("\nNo additional SKUs for @language @type found in Drupal. Nothing to delete.", [
          '@language' => $language->getName(),
          '@type' => $type,
        ]));
      }
    }

    $missing[$type]['all'] = array_unique($missing[$type]['all']);
    $to_be_deleted[$type]['all'] = array_unique($to_be_deleted[$type]['all']);
    $stock_price_mismatch_sync[$type] = !empty($stock_price_mismatch_sync[$type]) ? array_unique($stock_price_mismatch_sync[$type]) : [];
  }

  drush_print(dt("\n#### SYNCHRONIZATION ####"));

  $chunk_size = 100;

  // Sync store/price mis-match skus only for the merch report.
  if ($msource == 'report') {
    // Sync stock/price mis-match skus.
    foreach ($types as $type) {

      // Stock/Price mis-match sku sync only for simple skus.
      if ($type != 'simple') {
        continue;
      }

      if (!empty($stock_price_mismatch_sync[$type]) && drush_confirm(dt('Do you want to sync the @count @type stock/price mismatch SKUs?', [
        '@count' => count($stock_price_mismatch_sync[$type]),
        '@type' => $type,
      ]))) {
        foreach (\Drupal::service('acq_commerce.i18n_helper')->getStoreLanguageMapping() as $langcode => $store_id) {
          // We split the list of SKUs in small chunk to avoid any issue. This
          // is only to send the request to Conductor.
          foreach (array_chunk(str_replace("'", '', $stock_price_mismatch_sync[$type]), $chunk_size) as $chunk) {
            // @TODO: Make page size a config. It can be used in multiple places.
            // @TODO: It seems there is nothing being logged when fullSync is
            // launched.
            \Drupal::service('acq_commerce.ingest_api')->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          drush_print(dt('Sync launched for the @count @language @type SKUs with stock/price mismatch.', [
            '@count' => count($stock_price_mismatch_sync[$type]),
            '@language' => $languages[$langcode]->getName(),
            '@type' => $type,
          ]));
        }
      }
    }
  }

  // Retrieve missing SKUs.
  foreach ($types as $type) {
    if (!empty($missing[$type]['all']) && drush_confirm(dt('Do you want to sync the @count @type missing SKUs?', [
      '@count' => count($missing[$type]['all']),
      '@type' => $type,
    ]))) {
      foreach (\Drupal::service('acq_commerce.i18n_helper')->getStoreLanguageMapping() as $langcode => $store_id) {
        // We split the list of SKUs in small chunk to avoid any issue. This is
        // only to send the request to Conductor.
        foreach (array_chunk(str_replace("'", '', $missing[$type]['all']), $chunk_size) as $chunk) {
          // @TODO: Make page size a config. It can be used in multiple places.
          // @TODO: It seems there is nothing being logged when fullSync is
          // launched.
          \Drupal::service('acq_commerce.ingest_api')->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
        }

        drush_print(dt('Sync launched for the @count @language @type SKUs.', [
          '@count' => count($missing[$type]['all']),
          '@language' => $languages[$langcode]->getName(),
          '@type' => $type,
        ]));
      }
    }
  }

  // Try to resync extra SKUs.
  foreach ($types as $type) {
    if (!empty($to_be_deleted[$type]['all']) && drush_confirm(dt('Do you want to sync the @count @type extra SKUs?', [
      '@count' => count($to_be_deleted[$type]['all']),
      '@type' => $type,
    ]))) {
      foreach (\Drupal::service('acq_commerce.i18n_helper')->getStoreLanguageMapping() as $langcode => $store_id) {
        // We split the list of SKUs in small chunk to avoid any issue. This is
        // only to send the request to Conductor.
        foreach (array_chunk(str_replace("'", '', $to_be_deleted[$type]['all']), $chunk_size) as $chunk) {
          // @TODO: Make page size a config. It can be used in multiple places.
          // @TODO: It seems there is nothing being logged when fullSync is
          // launched.
          \Drupal::service('acq_commerce.ingest_api')->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
        }

        drush_print(dt('Sync launched for the @count @language @type SKUs.', [
          '@count' => count($to_be_deleted[$type]['all']),
          '@language' => $languages[$langcode]->getName(),
          '@type' => $type,
        ]));
      }
    }
  }

  if ($use_delete === FALSE) {
    return;
  }

  // Delete additional SKUs.
  // @TODO: Some of the code to delete node + sku is duplicate from
  // ProductSyncResource::post(). We might want a service to do that task and
  // remove code duplication.
  foreach ($types as $type) {
    if (!empty($to_be_deleted[$type]['all']) && drush_confirm(dt('Do you want to delete the @count @type additional SKUs?', [
      '@count' => count($to_be_deleted[$type]['all']),
      '@type' => $type,
    ]))) {
      $lock = \Drupal::lock();

      foreach ($languages as $langcode => $language) {
        foreach ($to_be_deleted[$type][$langcode] as $sku) {
          if ($sku_entity = SKU::loadFromSku($sku, $langcode, FALSE, TRUE)) {
            \Drupal::logger('alshaya_api')->notice('Removing disabled @language @type SKU @sku from the system: @sku.', [
              '@language' => $languages[$langcode]->getName(),
              '@type' => $type,
              '@sku' => $sku,
            ]);

            $lock_key = 'deleteProduct' . $sku;

            // Acquire lock to ensure parallel processes are executed
            // sequentially.
            // @TODO: These 8 lines might be duplicated in multiple places. We
            // may want to create a utility service in alshaya_performance.
            do {
              $lock_acquired = $lock->acquire($lock_key);

              // Sleep for half a second before trying again.
              // @TODO: Move this 0.5s to a config variable.
              if (!$lock_acquired) {
                usleep(500000);
              }
            } while (!$lock_acquired);

            // Delete the node if it is linked to this SKU only.
            try {
              if ($node = $sku_entity->getPluginInstance()->getDisplayNode($sku_entity, FALSE, FALSE)) {
                $node->delete();
              }
            }
            catch (\Exception $e) {
              // Not doing anything, we might not have node for the sku.
            }

            // Delete the SKU.
            $sku_entity->delete();

            // Release the lock.
            $lock->release($lock_key);

            drush_print(dt('Disabled @language @type SKU @sku removed from the system.', [
              '@language' => $languages[$langcode]->getName(),
              '@type' => $type,
              '@sku' => $sku,
            ]));
          }
        }
      }
    }
  }
}

/**
 * Implements drush sanity check visibility command.
 */
function drush_alshaya_api_sanity_check_visibility() {
  $verbose = drush_get_option('verbose', FALSE);

  $handle = \Drupal::service('alshaya_api.api')->getMerchandisingReport();

  if (!$handle) {
    drush_print(dt('Impossible to get the merchandising report from Magento.'));
    return;
  }

  // Because the column position may vary across brands, we are browsing the
  // report's first line to identify the position of each column we need.
  $indexes = [
    'partnum' => FALSE,
    'visibility' => FALSE,
  ];

  $not_visible_ind_skus = [];

  if ($data = fgetcsv($handle, 1000, ',')) {
    foreach ($data as $position => $key) {
      foreach ($indexes as $name => $index) {
        if (trim(strtolower($key)) == $name) {
          $indexes[$name] = $position;
          continue;
        }
      }
    }

    if (in_array(FALSE, $indexes)) {
      return FALSE;
    }

    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
      // This is a weird case where not visible SKU does not have any related
      // configurable.
      if (trim(strtolower($data[$indexes['visibility']])) == 'not visible individually') {
        $not_visible_ind_skus[] = $data[$indexes['partnum']];
      }
    }
  }
  fclose($handle);

  if (empty($not_visible_ind_skus)) {
    drush_print(dt('There is no SKU which are not visible individually as per the merchandising report.'));
    return;
  }

  $database = \Drupal::database();

  $query = $database->select('node__field_skus', 'n');
  $query->fields('n', ['entity_id']);
  $query->condition('n.bundle', 'acq_product');
  $query->condition('n.field_skus_value', $not_visible_ind_skus, 'IN');
  $nids = $query->execute()->fetchAllKeyed(0, 0);

  drush_print(dt('@count product nodes are related to SKUs which are not supposed to be visible individually!nids', [
    '@count' => count($nids),
    '!nids' => $verbose ? ":\n'" . implode("','", $nids) . "'" : '.',
  ]));

  if (!empty($nids) && drush_confirm(dt('Do you want to delete the @count product nodes?', [
    '@count' => count($nids),
  ]))) {
    $count = 1;
    foreach ($nids as $nid) {
      try {
        $storage = \Drupal::entityTypeManager()->getStorage('node');
        if ($node = $storage->load($nid)) {
          $node->delete();

          if ($count % 100 == 0) {
            drush_print(dt("Delete @count/@total...", [
              '@count' => $count,
              '@total' => count($nids),
            ]));
          }
          $count++;
        }
      }
      catch (\Exception $e) {
        \Drupal::logger('alshaya_api')->warning('Impossible to delete the product node @nid', ['@nid' => $nid]);
      }
    }
  }
}
