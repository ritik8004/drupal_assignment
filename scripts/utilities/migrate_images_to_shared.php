<?php

/**
 * @file
 * Script to migrate product images to shared brand folder.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush php-script scripts/utilities/migrate_images_to_shared.php`
 */

use Drupal\Core\File\FileSystemInterface;

$time_start = microtime(TRUE);

$logger = \Drupal::logger('migrate_images_to_shared');

$db = \Drupal::database();

$file_system = \Drupal::service('file_system');

// Get count of skus products public files.
$query = $db->select('acq_sku_field_data', 'sku');
$query->condition('default_langcode', 1);
$query->condition('attr_assets__value', '%public://%', 'LIKE');
$count = $query->countQuery()->execute()->fetchCol()[0];

if (empty($count)) {
  $logger->notice('No products found for migration.');
  exit;
}

$processed = 0;
$migrated = [];

do {
  // Get all the public files currently in use.
  $query = $db->select('acq_sku_field_data', 'sku');
  $query->addField('sku', 'sku');
  $query->addField('sku', 'attr_assets__value');
  $query->condition('default_langcode', 1);
  $query->condition('attr_assets__value', '%public://%', 'LIKE');
  $query->orderBy('changed', 'DESC');
  $query->range(0, 100);
  $result = $query->execute()->fetchAll();

  foreach ($result as $row) {
    $update = FALSE;

    // @codingStandardsIgnoreLine
    $media = unserialize($row->attr_assets__value);
    foreach ($media as $index => $item) {
      if (empty($item['drupal_uri']) || !str_contains($item['drupal_uri'], 'public://')) {
        continue;
      }

      $drupal_uri_original = $item['drupal_uri'];
      $item['drupal_uri'] = str_replace('public://', 'brand://', $drupal_uri_original);

      $update = TRUE;

      // Just update URI in SKU if it is already moved in another product.
      if (in_array($item['fid'], $migrated)) {
        $media[$index] = $item;
        continue;
      }

      $source = $file_system->realpath($drupal_uri_original);
      if (empty($source)) {
        $logger->notice(dt('File no longer available, removing from asset. SKU: @sku; Source: @source; Destination: @destination', [
          '@sku' => $row->sku,
          '@source' => $source,
          '@destination' => $item['drupal_uri'],
        ]));

        unset($item['fid']);
        unset($item['drupal_uri']);
        $media[$index] = $item;

        continue;
      }

      $directory = pathinfo($item['drupal_uri'], PATHINFO_DIRNAME);
      if (!$file_system->prepareDirectory($directory)) {
        $file_system->mkdir($directory, 0755, TRUE);
      }

      try {
        $file_system->move(
          $source,
          $directory,
          FileSystemInterface::EXISTS_REPLACE
        );

        $db->update('file_managed')
          ->fields(['uri' => $item['drupal_uri']])
          ->condition('fid', $item['fid'])
          ->execute();

        // Cache the file id which is already migrated to avoid moving
        // it again and again.
        $migrated[] = $item['fid'];

        $logger->notice(dt('File successfully moved. SKU: @sku; Source: @source; Destination: @destination', [
          '@sku' => $row->sku,
          '@source' => $source,
          '@destination' => $item['drupal_uri'],
        ]));
      }
      catch (\Exception) {
        $logger->notice(dt('File no longer available, removing from asset. SKU: @sku; Source: @source; Destination: @destination', [
          '@sku' => $row->sku,
          '@source' => $source,
          '@destination' => $item['drupal_uri'],
        ]));

        unset($item['fid']);
        unset($item['drupal_uri']);
      }

      $media[$index] = $item;
    }

    if ($update) {
      $db->update('acq_sku_field_data')
        ->fields(['attr_assets__value' => serialize($media)])
        ->condition('sku', $row->sku)
        ->execute();

      $logger->notice(dt('Updated from public to brand for sku @sku', [
        '@sku' => $row->sku,
      ]));
    }
  }

  $processed = $processed + (is_countable($result) ? count($result) : 0);
  $logger->notice(dt('Processed @processed out of total @count.', [
    '@processed' => $processed,
    '@count' => $count,
  ]));
} while ($result);

$time_end = microtime(TRUE);

$logger->notice(dt('Finished migration. Total time taken: @time, count: @count', [
  '@time' => ($time_end - $time_start),
  '@count' => $count,
]));
