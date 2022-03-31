<?php

/**
 * @file
 *
 * Script to remove downloaded product images.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush -l local.alshaya-mckw.com php-script scripts/utilities/remove-product-image-files.php`
 */

/** @var \Drupal\file\FileStorage $file_storage */
$file_storage = \Drupal::entityTypeManager()->getStorage('file');

$logger = \Drupal::logger('remove-product-image-files');

$db = \Drupal::database();

// Get all the files currently in use.
$query = $db->select('acq_sku_field_data', 'sku');
$query->addField('sku', 'media__value');
$query->addField('sku', 'sku');
$query->condition('media__value', '%fid%', 'like');
$result = $query->execute();

$used_fids = [];

foreach ($result->fetchAll() as $row) {
  $sku_needs_update = FALSE;

  $media = unserialize($row->media__value);
  foreach ($media as $index => $item) {
    if (!isset($item['fid'])) {
      continue;
    }

    try {
      $file = $file_storage->load($item['fid']);
      if ($file) {
        $file->delete();

        $logger->notice('Removed file with id: @id for sku: @sku.', [
          '@id' => $item['fid'],
          '@sku' => $row->sku,
        ]);
      }
    }
    catch (\Exception $e) {
      $logger->error('Failed to load/delete file with id: @id for sku: @sku, message: @message.', [
        '@id' => $item['fid'],
        '@sku' => $row->sku,
        '@message' => $e->getMessage(),
      ]);
    }

    $sku_needs_update = TRUE;
    unset($media[$index]['fid']);
  }

  if ($sku_needs_update) {
    $db->update('acq_sku_field_data')
      ->fields(['media__value' => serialize($media)])
      ->condition('sku', $row->sku)
      ->execute();

    $logger->notice('Downloaded files removed for SKU: @sku.', [
      '@sku' => $row->sku,
    ]);
  }
}

// Required as we did DB queries directly.
\Drupal::cache('entity')->deleteAll();
