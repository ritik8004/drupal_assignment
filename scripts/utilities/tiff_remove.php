<?php

/**
 * @file
 * Drush script to remove all TIFF files.
 */

/* @var \Drupal\file\FileStorage $fileStorage */
$fileStorage = \Drupal::entityTypeManager()->getStorage('file');
$logger = \Drupal::logger('tif_cleanup');

$query = $fileStorage->getQuery();
$query->condition('uri', '%.tif%', 'LIKE');
$files = $query->execute();

foreach ($files as $file_id) {
  /* @var \Drupal\file\Entity\File $file */
  $file = $fileStorage->load($file_id);
  if (empty($file)) {
    continue;
  }

  $logger->notice('Deleting file with id @id and URI @uri', [
    '@id' => $file_id,
    '@uri' => $file->getFileUri(),
  ]);

  $file->delete();
}
