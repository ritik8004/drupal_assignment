<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block for navigation links for department pages.
 *
 * @Block(
 *   id = "alshaya_dp_navigation_link",
 *   admin_label = @Translation("Alshaya Department Page Navigation Links"),
 * )
 */
class AlshayaDpNavigationLinks extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $categoryTree;

  /**
   * AlshayaDpNavigationLinks constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $category_tree
   *   Category tree.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ProductCategoryTree $category_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->categoryTree = $category_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_product_category.product_category_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = [];

    // @see MobileAppUtilityParagraphs::prepareAppNavigationLinks().
    if (isset($this->configuration['advanced_page_node'])) {
      $node = $this->configuration['advanced_page_node'];
    }
    else {
      $node = _alshaya_advanced_page_get_department_node();
    }

    // If department page, only then process further.
    if ($node instanceof NodeInterface) {
      // If term id is attached with node.
      if ($tid = $node->get('field_product_category')->first()->getString()) {
        // Get category tree data.
        $category_tree = $this->categoryTree->getCategoryTreeCached();
        if (isset($category_tree[$tid])) {
          // Prepare the L2 data.
          foreach ($category_tree[$tid]['child'] ?? [] as $l2_child) {
            if ($l2_child['app_navigation_link']) {
              $data['l2'][$l2_child['id']] = $l2_child['label'];
            }

            // Prepare L3 data from L2.
            foreach ($l2_child['child'] ?? [] as $l3_child) {
              if ($l3_child['app_navigation_link']) {
                $data['l3'][$l3_child['id']] = $l3_child['label'];
              }
            }
          }
        }
      }

      if (!empty($this->configuration['advanced_page_node'])) {
        return $data;
      }
    }

    return [
      '#theme' => 'alshaya_app_navigation_links',
      '#data' => $data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Discard cache for the block once a term gets updated.
    return Cache::mergeTags(parent::getCacheTags(), [ProductCategoryTree::CACHE_TAG]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // As each department page has different url.
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
