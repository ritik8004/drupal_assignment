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

// Get all nodes of product list which has english language.
use Drupal\node\NodeInterface;

$db = \Drupal::database();
$logger = \Drupal::logger('clean-brand-product-options');

$query = $db->select('node_field_data', 'nfd');
$query->addField('nfd', 'nid', 'nid');
$query->innerJoin('node', 'n', 'nfd.nid=n.nid');
$query->join('node__field_attribute_name', 'attr_name', 'attr_name.entity_id = n.nid');
$query->condition('attr_name.field_attribute_name_value', 'attr_brand');
$query->condition('n.type', 'product_list');
$nids = $query->execute()->fetchCol();

if (!empty($nids)) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  foreach ($nids as $nid) {
    try {
      if (($node = $node_storage->load($nid)) && ($node instanceof NodeInterface)) {
        $node->delete();
        $logger->notice('Deleting product option node: @nid', [
          '@nid' => $nid,
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
  $logger->notice('Total count: @count produt option nodes has been deleted', [
    '@count' => count($nids),
  ]);
}

// Re-save product options taxonomy terms after deleting nodes.
// This will create new nodes with correct urls.
$termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$query = $termStorage->getQuery();
$query->condition('vid', 'sku_product_option');
$result = $query->execute();

if (empty($result)) {
  return [];
}
$logger->notice('Re-saving all taxonomy terms of product options');
if ($result) {
  $shipping_options = $termStorage->loadMultiple($result);
  foreach ($shipping_options as $shipping_option) {
    $shipping_option->save();
  }
}
