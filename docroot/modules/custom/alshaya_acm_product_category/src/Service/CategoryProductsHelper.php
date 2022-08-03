<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\node\NodeInterface;
use Drupal\search_api\Query\ConditionGroup;

/**
 * Functions to help get products in a category from Indexed Data.
 *
 * @package Drupal\alshaya_acm_product_category\Service
 */
class CategoryProductsHelper {

  use LoggerChannelTrait;

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
   * Category listing page helper.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage
   */
  protected $productCategoryPage;

  /**
   * The language manger service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * CategoryProductsHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Alshaya SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage $product_category_page
   *   Category listing page helper.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   */
  public function __construct(SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ProductCategoryPage $product_category_page,
                              LanguageManagerInterface $language_manager,
                              Connection $database) {
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->productCategoryPage = $product_category_page;
    $this->languageManager = $language_manager;
    $this->database = $database;
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
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $term_details = $this->productCategoryPage->getCurrentSelectedCategory($langcode, $category_id);

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
        $query->setOption('algolia_options', ['ruleContexts' => $term_details['ruleContext']]);
        $results = $query->execute()->getResultItems();

        $nids = array_map(fn($result) => $result->getField('nid')->getValues()[0], $results);
      }
      catch (\Exception $e) {
        $this->getLogger('CategoryProductsHelper')->warning('Could not fetch data for carousel from Algolia because of reason: @message', [
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

  /**
   * Gets all the SKUs for products assigned to specified category.
   *
   * @param array $category_ids
   *   The taxonomy term ids.
   *
   * @return array
   *   The array of sku values.
   */
  public function getSkusForCategory(array $category_ids) {
    $query = $this->database->select('node__field_skus', 'nfs');
    $query->innerJoin('node__field_category', 'nfc', 'nfc.entity_id=nfs.entity_id');
    $query->condition('nfc.field_category_target_id', $category_ids, 'IN');
    $query->addField('nfs', 'field_skus_value', 'skus');
    $query->distinct();

    return $query->execute()->fetchCol();
  }

}
