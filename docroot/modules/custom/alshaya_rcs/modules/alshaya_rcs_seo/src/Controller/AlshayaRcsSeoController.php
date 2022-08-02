<?php

namespace Drupal\alshaya_rcs_seo\Controller;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Alshaya RCS seo Controller.
 */
class AlshayaRcsSeoController extends ControllerBase {

  /**
   * Product Category Tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaRcsSeoController constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product Category Tree.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *  Config factory.
   */
  public function __construct(
    ProductCategoryTree $product_category_tree,
    ConfigFactoryInterface $config_factory
  ) {
    $this->productCategoryTree = $product_category_tree;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('config.factory')
    );
  }

  /**
   * Controller for the site map.
   */
  public function siteMap() {
    return [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'rcs-ph-sitemap',
        'data-param-get-data' => 'false',
        'class' => ['block-rcs-ph-sitemap-block'],
        'data-param-entity-to-get' => 'navigation_menu',
        'data-param-category_id' => $this->configFactory->get('alshaya_rcs_main_menu.settings')->get('root_category'),
      ],
      '#attached' => [
        'library' => [
          'alshaya_white_label/sitemap',
          'alshaya_rcs_seo/sitemap'
        ],
      ],
    ];
  }
}
