<?php

/**
 * @file
 *
 * Script to clean un-used product images.
 *
 * This script does
 * - Finds and removes entries in file_usage for which SKU entities don't exist
 * - Finds all the files in file_managed that are not used in any SKU entity
 * - Removes these files from DB (file_managed) and file system.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush -l SITE_URL scr ..scripts/utilities/clean-product-images.php`
 */

ini_set('memory_limit', '1G');

$db = \Drupal::database();

$logger = \Drupal::logger('clean-product-images.php');

// Find all the entries in file_usage for which SKU entities are
// no longer available.
$query = $db->select('file_usage', 'fu');
$query->addField('fu', 'fid');
$query->addField('fu', 'id');
$query->leftJoin('acq_sku_field_data', 'asfd', 'asfd.id = fu.id');
$query->condition('fu.type', 'acq_sku');
$query->where('asfd.id IS NULL');
$result = $query->execute();

foreach ($result->fetchAll() as $row) {
  // We create log for each row for tracking.
  $logger->notice('Deleting file usage for fid: @fid for sku entity id: @sku_id', [
    '@fid' => $row->fid,
    '@sku_id' => $row->id,
  ]);

  $db->delete('file_usage')
    ->condition('fid', $row->fid)
    ->execute();
}

// Get all the files currently in use.
$query = $db->select('acq_sku_field_data', 'sku');
$query->addField('sku', 'media__value');
$query->condition('media__value', ['{}', '', 'a:0:{}'], 'NOT IN');
$result = $query->execute();

$used_fids = [];

foreach ($result->fetchAll() as $row) {
  $media = unserialize($row->media__value);

  foreach ($media as $item) {
    if (isset($item['fid'])) {
      $used_fids[$item['fid']] = $item['fid'];
    }
  }
}

// Get all the files currently there.
$query = $db->query("select fm.fid
  from file_managed fm
  left join file_usage fu on fm.fid = fu.fid
  where fm.uri like 'public://media/%'
    AND fu.fid is null ");
$result = $query->fetchAllKeyed(0, 0);

// Get the diff, remove files in use from all files.
$unwanted_files = array_diff($result, $used_fids);

$logger->notice('Found @total unwanted files', [
  '@total' => count($unwanted_files),
]);

$files_deleted = 0;

// We delete in chunks, if memory issues come we can start the script again.
foreach (array_chunk($unwanted_files, 200, TRUE) as $file_ids) {
  $files = \Drupal\file\Entity\File::loadMultiple($file_ids);
  \Drupal::entityTypeManager()->getStorage('file')->delete($files);

  $files_deleted += count($file_ids);

  $logger->notice('@count files deleted out of @total. FIDs: @fids.', [
    '@count' => $files_deleted,
    '@total' => count($unwanted_files),
    '@fids' => implode(',', $file_ids),
  ]);
}
