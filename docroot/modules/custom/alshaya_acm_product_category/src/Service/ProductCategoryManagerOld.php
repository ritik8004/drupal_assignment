<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Class ProductCategoryManagerOld.
 *
 * @package Drupal\alshaya_acm_product_category\Service
 */
class ProductCategoryManagerOld {

  const CATEGORIZATION_IDS_CACHE_TAG = 'alshaya_acm_categorization_ids';

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Cache to store ids of sale category tree.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * ProductCategoryManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache to store ids of sale category tree.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache) {
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->cache = $cache;
  }

  /**
   * Get sales category ids including children.
   *
   * @return array
   *   Sales category ids including child terms.
   *
   * @throws \Exception
   */
  public function getSalesCategoryIds(): array {
    // Return if enable_auto_sale_categorisation is set to FALSE.
    $config = $this->configFactory->get('alshaya_acm_product_category.settings');
    $enable_auto_sale_categorisation = $config->get('enable_auto_sale_categorisation');
    // Static cache.
    static $salesCategoryIds = NULL;
    if (is_array($salesCategoryIds)) {
      return $salesCategoryIds;
    }

    // Drupal cache.
    $cache = $this->cache->get(self::CATEGORIZATION_IDS_CACHE_TAG);
    if ($cache && $cache->data) {
      $salesCategoryIds = $cache->data;
      return $salesCategoryIds;
    }

    $salesCategoryIds = $config->get('sale_category_ids') ?? [];

    // Add tree.
    /** @var \Drupal\taxonomy\TermStorage $termStorage */
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $treeTids = [];
    foreach ($salesCategoryIds as $salesCategoryId) {
      $tree = $termStorage->loadTree(ProductCategoryTree::VOCABULARY_ID, $salesCategoryId);
      $treeTids = array_merge($treeTids, array_column($tree, 'tid'));
    }

    $salesCategoryIds = array_merge($salesCategoryIds, $treeTids);

    // Use cache tags of config.
    $tags = $config->getCacheTags();

    // Use custom cache tag to invalidate when any category
    // from sales tree is updated.
    $tags[] = self::CATEGORIZATION_IDS_CACHE_TAG;

    $this->cache->set(self::CATEGORIZATION_IDS_CACHE_TAG, $salesCategoryIds, Cache::PERMANENT, $tags);

    return $salesCategoryIds;
  }

  /**
   * Get Category IDs for a product.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product node.
   *
   * @return array
   *   Category ids from main field.
   */
  private function getProductCategoryIds(NodeInterface $node) {
    $values = $node->get('field_category')->getValue();
    return empty($values) ? [] : array_column($values, 'target_id');
  }

  /**
   * Get original Category IDs for a product.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product node.
   *
   * @return array
   *   Category ids from original field.
   */
  private function getProductOriginalCategoryIds(NodeInterface $node) {
    $values = $node->get('field_category_original')->getValue();
    return empty($values) ? [] : array_column($values, 'target_id');
  }

  /**
   * Check if product has sales category.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   *
   * @return bool
   *   TRUE if product has sales category.
   *
   * @throws \Exception
   */
  private function isOriginalProductInSaleCategory(NodeInterface $node) {
    $sales_category_ids = $this->getSalesCategoryIds();

    if (empty($sales_category_ids)) {
      return FALSE;
    }

    $product_category_ids = $this->getProductOriginalCategoryIds($node);

    if (array_intersect($sales_category_ids, $product_category_ids)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if product has special price.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   *
   * @return bool
   *   TRUE if product has special price.
   */
  public function isProductWithSpecialPrice(NodeInterface $node) {
    $sku = $node->get('field_skus')->getString();

    // We should never have this case but to avoid fatal error we do it.
    if (empty($sku)) {
      return FALSE;
    }

    $sku = SKU::loadFromSku($sku);

    // Again, we should never have this case but to avoid fatal error we do it.
    if (!($sku instanceof SKUInterface)) {
      return FALSE;
    }

    $prices = $this->skuManager->getMinPrices($sku);

    // If any children available.
    if (!empty($prices['children'])) {
      return max(array_column($prices['children'], 'discount')) > 0;
    }

    // This is the case for the simple skus. For simple skus, we don't have any
    // children and thus in this case we use `price` and `final_price`.
    return ($prices['price'] > 0) && ($prices['final_price'] > 0) && ($prices['price'] != $prices['final_price']);
  }

  /**
   * Wrapper function to remove non sale categories.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   *
   * @return bool
   *   TRUE if non sale categories were available and removed.
   *
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  private function removeNonSaleCategories(NodeInterface $node) {
    $sales_category_ids = $this->getSalesCategoryIds();

    if (empty($sales_category_ids)) {
      return FALSE;
    }

    $product_category_ids = $this->getProductOriginalCategoryIds($node);
    $product_non_sale_categories = array_intersect($product_category_ids, $sales_category_ids);

    // Load current value.
    $category_ids = $this->getProductCategoryIds($node);

    if ($category_ids != $product_non_sale_categories) {
      $node->get('field_category')->setValue($product_non_sale_categories);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Wrapper function to remove sale categories.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   *
   * @return bool
   *   TRUE if sale categories were available and removed.
   *
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  private function removeSaleCategories(NodeInterface $node) {
    $sales_category_ids = $this->getSalesCategoryIds();

    if (empty($sales_category_ids)) {
      return FALSE;
    }

    $product_category_ids = $this->getProductOriginalCategoryIds($node);
    $product_sale_categories = array_diff($product_category_ids, $sales_category_ids);

    // Load current value.
    $category_ids = $this->getProductCategoryIds($node);

    if ($category_ids != $product_sale_categories) {
      $node->get('field_category')->setValue($product_sale_categories);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Process sales re-categorisation.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   *
   * @return bool
   *   TRUE if node was updated.
   *
   * @throws \Exception
   */
  public function processSalesCategoryCheckForNode(NodeInterface $node) {
    // Do nothing if no sales category set.
    if (empty($this->getSalesCategoryIds())) {
      return FALSE;
    }

    $save = FALSE;

    // Do stuff only if it is in Sale Category as per MDC data.
    if ($this->isOriginalProductInSaleCategory($node)) {
      if ($this->isProductWithSpecialPrice($node)) {
        // If product is having special price remove all non-sales categories.
        $save = $this->removeNonSaleCategories($node);
      }
      else {
        // Else remove all sales categories.
        $save = $this->removeSaleCategories($node);
      }
    }

    return $save;
  }

  /**
   * Process sales re-categorisation.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   Main Product SKU or variant SKU.
   *
   * @throws \Exception
   */
  public function processSalesCategoryCheckForSku(SKUInterface $sku) {
    // Do nothing if no sales category set.
    if (empty($this->getSalesCategoryIds())) {
      return;
    }

    $node = $this->skuManager->getDisplayNode($sku);

    if ($node instanceof NodeInterface) {
      if ($this->processSalesCategoryCheckForNode($node)) {
        $node->save();

        // Reset static cache to ensure we use updated node in later
        // code execution.
        // @see Drupal\acq_sku\AcquiaCommerce\SKUPluginBase::getDisplayNode().
        drupal_static_reset('getDisplayNode');
      }
    }
  }

}
