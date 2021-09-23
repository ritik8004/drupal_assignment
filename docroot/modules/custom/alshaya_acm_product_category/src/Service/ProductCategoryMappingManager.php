<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\CategoryRepositoryInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\node\NodeInterface;

/**
 * Class Product Category Mapping Manager.
 *
 * @package Drupal\alshaya_acm_product_category\Service
 */
class ProductCategoryMappingManager {

  use LoggerChannelTrait;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Category Repository.
   *
   * @var \Drupal\acq_sku\CategoryRepositoryInterface
   */
  protected $categoryRepo;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * ProductCategoryMappingManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\acq_sku\CategoryRepositoryInterface $category_repo
   *   Category Repository.
   */
  public function __construct(SkuManager $sku_manager,
                              CategoryRepositoryInterface $category_repo) {
    $this->skuManager = $sku_manager;
    $this->categoryRepo = $category_repo;

    $this->logger = $this->getLogger('ProductCategoryMappingManager');
  }

  /**
   * Wrapper function to map categories to product.
   *
   * @param string $sku
   *   SKU as string.
   * @param array $category_commerce_ids
   *   Commerce Category IDs.
   */
  public function mapCategoriesToProduct(string $sku, array $category_commerce_ids) {
    $sku_entity = SKU::loadFromSku($sku);
    if (!($sku_entity instanceof SKUInterface)) {
      $this->logger->warning('Skipped mapping update as SKU entity not available for SKU @sku', [
        '@sku' => $sku,
      ]);
      return;
    }

    $node = $this->skuManager->getDisplayNode($sku_entity, FALSE);
    if (!($node instanceof NodeInterface)) {
      $this->logger->warning('Skipped mapping update as Node not available for SKU @sku', [
        '@sku' => $sku,
      ]);
      return;
    }

    $categories_new = $this->categoryRepo->getTermIdsFromCommerceIds($category_commerce_ids);
    $categories_existing = array_column($node->get('field_category_original')->getValue(), 'target_id');

    if (empty($categories_new) && empty($categories_existing)) {
      // Skip if both are empty.
      $this->logger->notice('Category mapping skipped as both existing and new are empty for SKU @sku', [
        '@sku' => $sku,
      ]);
      return;
    }
    elseif (empty($categories_new)
      || empty($categories_existing)
      || array_diff($categories_new, $categories_existing)
      || array_diff($categories_existing, $categories_new)) {
      // Do nothing here, we will update.
    }
    else {
      // No diff found, we skip.
      $this->logger->notice('Category mapping skipped as there is no change for SKU @sku; New: @new; Existing: @existing', [
        '@sku' => $sku,
        '@new' => implode(',', $categories_new),
        '@existing' => implode(',', $categories_existing),
      ]);

      return;
    }

    $node->get('field_category')->setValue($categories_new);
    $node->get('field_category_original')->setValue($node->get('field_category')->getValue());
    $node->save();

    // Trigger update to ensure all post updated event subscribers
    // are invoked.
    _alshaya_acm_product_post_sku_operation($sku_entity, ProductUpdatedEvent::EVENT_UPDATE);

    $this->logger->notice('Category mapping updated for SKU @sku; New: @new; Existing: @existing', [
      '@sku' => $sku,
      '@new' => implode(',', $categories_new),
      '@existing' => implode(',', $categories_existing),
    ]);
  }

}
