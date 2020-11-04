<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\search_api\Query\ConditionGroup;

/**
 * Functions to help get products in a category from Indexed Data.
 *
 * @package Drupal\alshaya_acm_product\Service
 */
class CategoryProductsHelper {

  /**
   * Alshaya SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CategoryProductsHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Alshaya SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get unique products for category.
   *
   * Get it from indexed data in the same way as Category Listing page.
   *
   * @param int $category_id
   *   Category id.
   * @param int $limit
   *   Limit.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of nodes.
   */
  public function getProductsInCategory(int $category_id, int $limit) {
    // First check if database index is enabled, DB query is cheaper
    // then API call.
    if (AlshayaSearchApiHelper::isIndexEnabled('product')) {
      return $this->getProductsInCategoryDatabase($category_id, $limit);
    }

    // Check if Algolia is enabled.
    if (AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_index')) {
      return $this->getProductsInCategoryAlgolia($category_id, $limit);
    }

    return [];
  }

  /**
   * Get unique products for category from Database index.
   *
   * Get it from indexed data in the same way as Category Listing page.
   *
   * @param int $category_id
   *   Category id.
   * @param int $limit
   *   Limit.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of nodes.
   */
  protected function getProductsInCategoryDatabase(int $category_id, int $limit) {
    $terms = _alshaya_master_get_recursive_child_terms($category_id);
    $arguments = [
      'tid' => implode('+', $terms),
    ];

    $unique_nodes = [];
    $page = 0;

    do {
      $results = _alshaya_master_get_views_result('alshaya_product_list', 'block_1', $arguments, $page);

      $nodes = array_map(function ($result) {
        if (($node = $result->_object->getValue()) && $node instanceof NodeInterface) {
          $color = $node->get('field_product_color')->getString();

          if ($color) {
            $node = $this->skuManager->getDisplayNode(
              $this->skuManager->getSkuForNode($node),
              FALSE
            );
          }

          return $node;
        }
      }, $results);

      $nodes = alshaya_acm_product_filter_out_of_stock_products($nodes, $limit);
      foreach ($nodes as $node) {
        $unique_nodes[$node->id()] = $node;
      }

      $page++;
    } while (!empty($nodes) && count($unique_nodes) < $limit);

    $unique_nodes = array_slice($unique_nodes, 0, $limit);

    return $unique_nodes;
  }

  /**
   * Get unique products for category from Algolia index.
   *
   * Get it from indexed data in the same way as Category Listing page.
   *
   * @param int $category_id
   *   Category id.
   * @param int $limit
   *   Limit.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of nodes.
   */
  protected function getProductsInCategoryAlgolia(int $category_id, int $limit) {
    $unique_nodes = [];
    $page = 0;
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $term_details = \Drupal::service('alshaya_acm_product_category.page')->getCurrentSelectedCategory($langcode, $category_id);

    $index = $this->entityTypeManager
      ->getStorage('search_api_index')
      ->load('alshaya_algolia_index');

    do {
      try {
        $query = $index->query();
        $query->range($page, $page * $limit);
        $conditionGroup = new ConditionGroup();
        $conditionGroup->addCondition('stock', 0, '>');
        $conditionGroup->addCondition($term_details['category_field'], '"' . $term_details['hierarchy'] . '"');
        $query->addConditionGroup($conditionGroup);
        $query->setOption('attributesToRetrieve', ['nid']);
        $query->setOption('ruleContexts', $term_details['ruleContext']);
        $results = $query->execute()->getResultItems();

        $nids = array_map(function ($result) {
          return $result->getField('nid')->getValues()[0];
        }, $results);
      }
      catch (\Exception $e) {
        \Drupal::logger('alshaya_acm_product')->notice('Could not fetch data for carousel from Algolia because of reason: @message', [
          '@message' => $e->getMessage(),
        ]);

        return [];
      }

      $storage = $this->entityTypeManager->getStorage('node');

      $nodes = array_map(function ($nid) use ($storage) {
        if (($node = $storage->load($nid)) && ($node instanceof NodeInterface)) {
          $color = $node->get('field_product_color')->getString();

          if ($color) {
            $node = $this->skuManager->getDisplayNode(
              $this->skuManager->getSkuForNode($node),
              FALSE
            );
          }

          return $node;
        }
      }, $nids);

      $nodes = alshaya_acm_product_filter_out_of_stock_products($nodes, $limit);
      foreach ($nodes as $node) {
        $unique_nodes[$node->id()] = $node;
      }

      $page++;
    } while (!empty($nodes) && count($unique_nodes) < $limit);

    $unique_nodes = array_slice($unique_nodes, 0, $limit);

    return $unique_nodes;
  }

}
