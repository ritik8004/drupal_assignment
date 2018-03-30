<?php

namespace Drupal\alshaya_seo_transac\Controller;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaSeoController.
 */
class AlshayaSeoController extends ControllerBase {

  /**
   * Product Category Tree service object.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * AlshayaSeoController constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product Category Tree service object.
   */
  public function __construct(ProductCategoryTree $product_category_tree) {
    $this->productCategoryTree = $product_category_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product_category.product_category_tree')
    );
  }

  /**
   * Controller for the site map.
   */
  public function siteMap() {
    $data = $this->productCategoryTree->getCategoryTreeCached();

    $build = [
      '#theme' => 'alshaya_sitemap',
      '#term_tree' => $data,
    ];

    // Discard cache for the page once a term gets updated.
    $build['#cache']['tags'][] = ProductCategoryTree::CACHE_TAG;

    return $build;
  }

}
