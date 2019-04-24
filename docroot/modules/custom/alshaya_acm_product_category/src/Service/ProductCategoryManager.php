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
 * Class ProductCategoryManager.
 *
 * @package Drupal\alshaya_acm_product_category\Service
 */
class ProductCategoryManager {

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
   * Get sales/new-arrival category ids including children.
   *
   * @return array
   *   Sales or new arrival category ids including child terms.
   *
   * @throws \Exception
   */
  public function getCategorizationIds(): array {
    // Static cache.
    static $categorizationIds = NULL;
    if (is_array($categorizationIds)) {
      return $categorizationIds;
    }

    // Drupal cache.
    $cache = $this->cache->get('alshaya_acm_categorization_ids');
    if ($cache && $cache->data) {
      $categorizationIds = $cache->data;
      return $categorizationIds;
    }

    $config = $this->configFactory->get('alshaya_acm_product_category.settings');
    $salesCategoryIds = $config->get('sale_category_ids') ?? [];
    $newArrivalCategoryIds = $config->get('new_arrival_category_ids') ?? [];

    // Add tree.
    /** @var \Drupal\taxonomy\TermStorage $termStorage */
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $treeTids = [];
    foreach ($salesCategoryIds as $salesCategoryId) {
      $tree = $termStorage->loadTree(ProductCategoryTree::VOCABULARY_ID, $salesCategoryId);
      $treeTids = array_merge($treeTids, array_column($tree, 'tid'));
    }
    $salesCategoryIds = array_merge($salesCategoryIds, $treeTids);

    $treeTids = [];
    foreach ($newArrivalCategoryIds as $newArrivalCategoryId) {
      $tree = $termStorage->loadTree(ProductCategoryTree::VOCABULARY_ID, $newArrivalCategoryId);
      $treeTids = array_merge($treeTids, array_column($tree, 'tid'));
    }
    $newArrivalCategoryIds = array_merge($newArrivalCategoryIds, $treeTids);

    // Use cache tags of config.
    $tags = $config->getCacheTags();

    // Use custom cache tag to invalidate when any category
    // from sales tree is updated.
    $tags[] = self::CATEGORIZATION_IDS_CACHE_TAG;

    $categorizationIds = [
      'sale' => $salesCategoryIds,
      'new_arrival' => $newArrivalCategoryIds,
    ];

    $this->cache->set('alshaya_acm_categorization_ids', $categorizationIds, Cache::PERMANENT, $tags);

    return $categorizationIds;
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
   * Check if product has sales/new-arrival category.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   *
   * @return bool
   *   TRUE if product has sales/new-arrival category.
   *
   * @throws \Exception
   */
  private function isOriginalProductCategorized(NodeInterface $node) {
    // Get categories for the categorization.
    $categorization_ids = $this->getCategorizationIds();

    if (empty(array_filter($categorization_ids))) {
      return FALSE;
    }

    $product_category_ids = $this->getProductOriginalCategoryIds($node);

    // If product has any sale category or its child.
    if (array_intersect($categorization_ids['sale'], $product_category_ids)) {
      return TRUE;
    }

    // If product has any new-arrival category or its child.
    if (array_intersect($categorization_ids['new_arrival'], $product_category_ids)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Wrapper function to remove non sale/new-arrival categories.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   *
   * @return bool
   *   TRUE if non sale/new-arrival categories were available and removed.
   *
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  private function removeNonSaleNewArrivalCategories(NodeInterface $node) {
    $categorization_ids = $this->getCategorizationIds();

    if (empty(array_filter($categorization_ids))) {
      return FALSE;
    }

    $product_category_ids = $this->getProductOriginalCategoryIds($node);

    $cat_ids = [];

    if ($this->isProductWithSalesOrNewArrival($node, ['attr_is_sale'])) {
      $cat_ids = array_merge($cat_ids, $categorization_ids['sale']);
    }

    if ($this->isProductWithSalesOrNewArrival($node, ['attr_is_new'])) {
      $cat_ids = array_merge($cat_ids, $categorization_ids['new_arrival']);
    }

    // If still its empty, then don't process further. This might be the case
    // for example - when sales category is configured but `is_sale` is false.
    if (empty($cat_ids)) {
      return FALSE;
    }

    $non_sale_new_arrival_categories = array_intersect($product_category_ids, $cat_ids);

    // Load current value.
    $category_ids = $this->getProductCategoryIds($node);

    if ($category_ids != $non_sale_new_arrival_categories) {
      $node->get('field_category')->setValue($non_sale_new_arrival_categories);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Wrapper function to remove sale/new-arrival categories.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product Node.
   *
   * @return bool
   *   TRUE if sale/new-arrival categories were available and removed.
   *
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  private function removeSaleNewArrivalCategories(NodeInterface $node) {
    $categorization_ids = $this->getCategorizationIds();

    if (empty(array_filter($categorization_ids))) {
      return FALSE;
    }

    $cat_ids = [];

    if ($this->isProductWithSalesOrNewArrival($node, ['attr_is_sale'])) {
      $cat_ids = array_merge($cat_ids, $categorization_ids['sale']);
    }

    if ($this->isProductWithSalesOrNewArrival($node, ['attr_is_new'])) {
      $cat_ids = array_merge($cat_ids, $categorization_ids['new_arrival']);
    }

    // If still its empty, then don't process further. This might be the case
    // for example - when sales category is configured but `is_sale` is false.
    if (empty($cat_ids)) {
      return FALSE;
    }

    $product_category_ids = $this->getProductOriginalCategoryIds($node);
    $sale_new_arrival_categories = array_diff($product_category_ids, $cat_ids);

    // Load current value.
    $category_ids = $this->getProductCategoryIds($node);

    if ($category_ids != $sale_new_arrival_categories) {
      $node->get('field_category')->setValue($sale_new_arrival_categories);
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
  public function processCategorizationCheckForNode(NodeInterface $node) {
    // Do nothing if no sales/new-arrival category set.
    if (empty(array_filter($this->getCategorizationIds()))) {
      return FALSE;
    }

    $save = FALSE;

    // Do stuff only if it is in Sale/New-arrival Category as per MDC data.
    if ($this->isOriginalProductCategorized($node)) {
      if ($this->isProductWithSalesOrNewArrival($node)) {
        // Remove all non sales/new-arrival categories.
        $save = $this->removeNonSaleNewArrivalCategories($node);
      }
      else {
        // Else remove all sales/new-arrival categories.
        $save = $this->removeSaleNewArrivalCategories($node);
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

    if ($sku->bundle() === 'simple') {
      $node = $this->skuManager->getDisplayNode($sku);

      if ($node instanceof NodeInterface) {
        if ($this->processCategorizationCheckForNode($node)) {
          $node->save();

          // Reset static cache to ensure we use updated node in later
          // code execution.
          // @see Drupal\acq_sku\AcquiaCommerce\SKUPluginBase::getDisplayNode().
          drupal_static_reset('getDisplayNode');
        }
      }
    }
  }

  /**
   * Determines if product sku has `is_sale` or `is_new` set or not.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product node object.
   * @param array $attributes
   *   Attributes (is_new / is_sale) to check.
   *
   * @return bool
   *   If product sku has `is_sale` / 'is_new' attribute true.
   */
  public function isProductWithSalesOrNewArrival(NodeInterface $node, array $attributes = []) {
    // Get the attached sku with the node.
    $sku = $node->get('field_skus')->first()->getString();
    $sku = SKU::loadFromSku($sku);
    $return = FALSE;
    if ($sku instanceof SKUInterface) {
      if (!$attributes) {
        $attributes = [
          'attr_is_sale',
          'attr_is_new',
        ];
      }

      foreach ($attributes as $attribute) {
        if ($attr = $sku->get($attribute)->getValue()) {
          $return = (bool) $attr[0]['value'];
          if ($return) {
            break;
          }
        }
      }
    }

    return $return;
  }

}
