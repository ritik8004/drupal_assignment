<?php

/**
 * @file
 *
 * Script to clean product images.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush -l local.alshaya-mckw.com php-script scripts/utilities/clean-product-images.php`
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
$condition_group = $query->orConditionGroup();
$condition_group->condition('price', 0.01);
$condition_group->condition('final_price', 0.01);
$query->condition($condition_group);
$query->condition('media__value', '%fid%', 'NOT LIKE');
$result = $query->execute();

$skus = array_column($result->fetchAll(), 'sku');
$logger->notice('Found free gift skus without fid. SKUs: @skus', [
  '@skus' => implode(', ', $skus),
]);

foreach ($skus as $sku_string) {
  $sku = SKU::loadFromSku($sku_string);
  if (!($sku instanceof SKU)) {
    continue;
  }

  $logger->notice('Download images for free gift sku: @sku', [
    '@sku' => $sku->getSku(),
  ]);

  $sku_tags = ProductCacheManager::getAlshayaProductTags($sku);

  Cache::invalidateTags($sku->getCacheTags());
  Cache::invalidateTags($sku_tags);

  foreach ($sku->getTranslationLanguages() as $language) {
    $translation = $sku->getTranslation($language->getId());
    $imagesManager->getProductMedia($translation, 'pdp', TRUE);
    $imagesManager->getProductMedia($translation, 'pdp', FALSE);
  }
}
