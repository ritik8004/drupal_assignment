<?php

/**
 * @file
 *
 * Script to remove downloaded product images.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush -l local.alshaya-mckw.com php-script scripts/utilities/clean-file-usage.php`
 */

$db = \Drupal::database();

// Get all the un-used entries in file_usage.
$query = $db->select('file_usage', 'fu');
$query->addField('fu', 'fid');
$query->leftJoin('file_managed', 'fm', 'fu.fid = fm.fid');
$query->isNull('fm.fid');
$result = $query->execute()->fetchAllKeyed(0, 0);

// Delete all the file_usage entries.
$db->delete('file_usage')
  ->condition('fid', $result, 'IN')
  ->execute();
