<?php

/**
 * @file
 * Script to clean brand product options.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush -l local.alshaya-bpae.com
 * php-script scripts/utilities/clean-brand-product-options.php`
 */

use Drupal\node\NodeInterface;

$db = \Drupal::database();
$logger = \Drupal::logger('clean-brand-product-options');

// Get all nodes of product list type with attribute brand.
$query = $db->select('node_field_data', 'nfd');
$query->addField('nfd', 'nid', 'nid');
$query->innerJoin('node', 'n', 'nfd.nid=n.nid');
$query->join('node__field_attribute_name', 'attr_name', 'attr_name.entity_id = n.nid');
$query->condition('attr_name.field_attribute_name_value', 'attr_brand');
$query->condition('n.type', 'product_list');
$nids = $query->execute()->fetchCol();

if (empty($nids)) {
  $logger->notice('No nodes found for brand product options.');
  return;
}

$node_storage = \Drupal::entityTypeManager()->getStorage('node');
$count = 0;
foreach ($nids as $nid) {
  try {
    $node = $node_storage->load($nid);
    // Checking for english language nodes which has title in arabic.
    // Delete all those nodes as they are duplicate and not required.
    if ($node instanceof NodeInterface && $node->language()->getId() === 'en' && preg_match("/\p{Arabic}/u", $node->getTitle())) {
      $node->delete();
      $count++;
      $logger->notice('Deleted product option node: @nid with arabic title: @title', [
        '@nid' => $nid,
        '@title' => $node->getTitle(),
      ]);
    }
  }
  catch (\Exception $e) {
    $logger->error('Error while deleting product options node: @nid Message: @message', [
      '@nid' => $nid,
      '@message' => $e->getMessage(),
    ]);
  }
}
$logger->notice('Total count: @count product option nodes has been deleted', [
  '@count' => $count,
]);
