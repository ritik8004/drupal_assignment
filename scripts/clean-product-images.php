<?php

/**
 * @file
 *
 * Script to clean product images.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush @alshaya.local -l local.alshaya-mckw.com php-script ../scripts/clean-product-images.php`
 */

$db = \Drupal::database();

// Get all the files currently in use.
$query = $db->select('acq_sku_field_data', 'sku');
$query->addField('sku', 'media__value');
$query->condition('media__value', ['{}', '', 'a:0:{}'], 'NOT IN');
$result = $query->execute();

$used_fids = [];

foreach ($result->fetchAll() as $row) {
  $media = unserialize($row->media__value);
  foreach ($media as $item) {
    if (isset($item['file']) && $item['file'] instanceof \Drupal\file\Entity\File) {
      $used_fids[$item['file']->id()] = $item['file']->id();
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

drush_print(dt('Found @total unwanted files', [
  '@total' => count($unwanted_files),
]));

$files_deleted = 0;

// We delete in chunks, if memory issues come we can start the script again.
foreach (array_chunk($unwanted_files, 200, TRUE) as $file_ids) {
  $files = \Drupal\file\Entity\File::loadMultiple($file_ids);
  \Drupal::entityTypeManager()->getStorage('file')->delete($files);

  $files_deleted += count($file_ids);
  drush_print(dt('@count files deleted.', [
    '@count' => $files_deleted,
  ]));
}
