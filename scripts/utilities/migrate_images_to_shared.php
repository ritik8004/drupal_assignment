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

$logger = \Drupal::logger('migrate_images_to_shared');

$db = \Drupal::database();

/* @var \Drupal\Core\File\FileSystem $file_system */
$file_system = \Drupal::service('file_system');

// Get all the files currently in use.
$query = $db->select('acq_sku_field_data', 'sku');
$query->addField('sku', 'sku');
$query->addField('sku', 'attr_assets__value');
$query->condition('default_langcode', 1);
$query->condition('attr_assets__value', '%public://%', 'LIKE');
$query->orderBy('changed', 'DESC');
$query->range(0, 1000);
$result = $query->execute()->fetchAll();

foreach ($result as $row) {
  $update = FALSE;

  $media = unserialize($row->attr_assets__value);
  foreach ($media as $index => $item) {
    if (empty($item['drupal_uri']) || strpos($item['drupal_uri'], 'public://') === FALSE) {
      continue;
    }
    $update = TRUE;

    $source = $file_system->realpath($item['drupal_uri']);
    if (empty($source)) {
      unset($item['fid']);
      unset($item['drupal_uri']);
      $media[$index] = $item;

      continue;
    }

    $item['drupal_uri'] = str_replace('public://', 'brand://', $item['drupal_uri']);

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

      $logger->notice(dt('File successfully moved. SKU: @sku; Source: @source; Destination: @destination', [
        '@sku' => $row->sku,
        '@source' => $source,
        '@destination' => $item['drupal_uri'],
      ]));
    }
    catch (\Exception $e) {
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
