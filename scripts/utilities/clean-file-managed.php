<?php

/**
 * @file
 *
 * Script to remove un-used product images.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush -l local.alshaya-mckw.com php-script scripts/utilities/clean-file-managed.php`
 */

/** @var \Drupal\file\FileStorage $file_storage */
$file_storage = \Drupal::entityTypeManager()->getStorage('file');

$logger = \Drupal::logger('remove-unused-files');

$db = \Drupal::database();

// Get all the media files.
$query = $db->select('file_managed', 'fm');
$query->addField('fm', 'fid');
$query->addField('fm', 'uri');
$query->condition('fm.uri', 'public://media%', 'LIKE');
$result = $query->execute()->fetchAllKeyed();

foreach ($result as $fid => $uri) {
  try {
    $file = $file_storage->load($fid);
    if ($file) {
      $file->delete();

      $logger->notice('Removed file with id: @id having uri: @uri.', [
        '@id' => $fid,
        '@uri' => $uri,
      ]);
    }
  }
  catch (\Exception $e) {
    $logger->error('Failed to load/delete file with id: @id for uri: @uri, message: @message.', [
      '@id' => $fid,
      '@uri' => $uri,
      '@message' => $e->getMessage(),
    ]);
  }
}
