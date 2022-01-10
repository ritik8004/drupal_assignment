<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;

/**
 * Product category carousel helper service.
 */
class ProductCategoryCarouselHelper implements ProductCategoryCarouselHelperInterface {

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Product category page.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage
   */
  protected $productCategoryPage;

  /**
   * Constructs object of ProductCategoryCarouselHelper.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage $product_category_page
   *   Product category page.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory,
    ProductCategoryPage $product_category_page
  ) {
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->productCategoryPage = $product_category_page;
  }

  /**
   * {@inheritdoc}
   */
  public function getCarousel($category_id, int $carousel_limit, $carousel_title, $view_all_text) {
    $carousel = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $productCarousel = $this->productCategoryPage->getCurrentSelectedCategory($langcode, $category_id);
    $settings = $this->configFactory->get('alshaya_acm_product.settings');

    // Make carousel title link.
    $carousel_title = [
      'title' => $carousel_title,
      'url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $category_id])->toString(),
    ];

    $productCarousel = array_merge($productCarousel, [
      'sectionTitle' => $carousel_title,
      'itemsPerPage' => $carousel_limit,
      'vatText' => $settings->get('vat_text'),
    ]);

    $carousel['content']['product_category_carousel'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['alshaya-product-category-carousel'],
        'data-pcc-id' => $category_id,
      ],
      '#attached' => [
        'drupalSettings' => [
          'alshayaProductCarousel' => [
            $category_id => $productCarousel,
          ],
          'hp_product_carousel_items' => $settings->get('product_carousel_items_settings.hp_product_carousel_items_number'),
        ],
        'library' => [
          'alshaya_algolia_react/product_category_carousel',
          'alshaya_white_label/product_carousel',
        ],
      ],
    ];

    if ($view_all_text) {
      $carousel['attributes']['class'][] = 'has-view-all-link';

      $carousel['content']['view_all'] = [
        '#title' => $view_all_text,
        '#type' => 'link',
        '#attributes' => [
          'class' => ['category-carousel-view-all'],
        ],
        '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $category_id]),
      ];
    }

    return $carousel;
  }

}
