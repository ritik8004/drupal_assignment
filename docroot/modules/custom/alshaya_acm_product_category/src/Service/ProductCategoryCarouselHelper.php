<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;

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
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs object of ProductCategoryCarouselHelper.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage $product_category_page
   *   Product category page.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory,
    ProductCategoryPage $product_category_page,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->productCategoryPage = $product_category_page;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Create and returns the render array for category carousel accordion.
   *
   * @param int $category_id
   *   Category id.
   * @param string $carousel_title
   *   Title for the carousel.
   * @param string $view_all_text
   *   View all text.
   *
   * @return array
   *   The render array for carousel accordion.
   */
  private function getCarouselAccordion($category_id, $carousel_title, $view_all_text) {
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($category_id);
    // If given category not available.
    if (!$term instanceof TermInterface) {
      return [];
    }

    if (empty($carousel_title)) {
      $carousel_title = $term->label();
    }

    // Create accordion title link.
    $accordion_title = [
      '#type' => 'link',
      '#title' => $carousel_title,
      '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $category_id]),
    ];

    $link = [];
    if ($view_all_text) {
      $link = [
        '#title' => $view_all_text,
        '#type' => 'link',
        '#attributes' => [
          'class' => ['category-accordion-view-all'],
        ],
        '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $category_id]),
      ];
    }

    // Theme content as accordion.
    $carousel['content']['product_category_carousel'] = [
      '#theme' => 'alshaya_white_label_accordion',
      '#title' => $accordion_title ?: NULL,
      '#content' => alshaya_acm_product_category_child_terms($category_id),
      '#view_all' => $link,
      '#cache' => [
        'tags' => [
          ProductCategoryTree::CACHE_TAG,
        ],
      ],
      '#attached' => [
        'library' => [
          'alshaya_white_label/product-category-accordion',
        ],
      ],
    ];

    return $carousel;
  }

  /**
   * {@inheritdoc}
   */
  public function getCarousel($category_id, int $carousel_limit, $carousel_title, $view_all_text, $is_accordion) {
    // By default we don't show any carousel content.
    $carousel['content'] = [];

    if ($is_accordion) {
      $accordion_content = $this->getCarouselAccordion($category_id, $carousel_title, $view_all_text);
      return array_merge($carousel, $accordion_content);
    }

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
