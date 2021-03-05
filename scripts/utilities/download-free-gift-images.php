<?php

/**
 * @file
 *
 * Script to download images for free gift products.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush -l local.alshaya-mckw.com php-script ../scripts/utilities/download-free-gift-images.php
 */

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\Core\Cache\Cache;

$logger = \Drupal::logger('download-free-gift-images');

$db = \Drupal::database();

/** @var \Drupal\alshaya_acm_product\SkuImagesManager $imagesManager */
$imagesManager = \Drupal::service('alshaya_acm_product.sku_images_manager');

// Get all the files currently in use.
$query = $db->select('acq_sku_field_data', 'sku');
$query->addField('sku', 'sku');
$query->addField('sku', 'langcode');

$query->condition('media__value', '%fid%', 'NOT LIKE');

$condition_group = $query->orConditionGroup();
$condition_group->condition('price', 0.01);
$condition_group->condition('final_price', 0.01);
$query->condition($condition_group);

$result = $query->execute();

foreach ($result->fetchAll() as $row) {
  $sku = SKU::loadFromSku($row->sku, $row->langcode);
  if (!($sku instanceof SKU) || $sku->language()->getId() != $row->langcode) {
    continue;
  }

  $logger->notice('Download images for free gift sku: @sku, langcode: @langcode', [
    '@sku' => $sku->getSku(),
    '@langcode' => $sku->language()->getId(),
  ]);

  $sku_tags = ProductCacheManager::getAlshayaProductTags($sku);

  Cache::invalidateTags($sku->getCacheTags());
  Cache::invalidateTags($sku_tags);

  $imagesManager->getProductMedia($sku, 'pdp', TRUE);
  $imagesManager->getProductMedia($sku, 'pdp', FALSE);
}
