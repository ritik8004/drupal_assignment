<?php

namespace Drupal\alshaya_rcs_listing\Service;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryCarouselHelper as ProductCategoryCarouselHelperOriginal;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryPage;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Product category carousel helper service.
 */
class ProductCategoryCarouselHelper extends ProductCategoryCarouselHelperOriginal {

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
   * Gets the value of the slug field.
   *
   * @return string
   *   The value of the slug field.
   */
  private function getSlug() {
    return $this->entity->get('field_category_carousel_slug')->getString();
  }

  /**
   * Create and returns the render array for category carousel accordion.
   *
   * @return array
   *   The render array for carousel accordion.
   */
  private function getCarouselAccordion() {
    $carousel_title = $this->getCarouselTitle();

    // Create accordion title link.
    $accordion_title = [
      '#type' => 'link',
      '#title' => $carousel_title,
      '#url' => '',
    ];

    $link = [];
    $view_all_text = $this->getViewAllText();
    if ($view_all_text) {
      $link = [
        '#title' => $view_all_text,
        '#type' => 'link',
        '#attributes' => [
          'class' => ['category-accordion-view-all'],
        ],
        '#url' => '',
      ];
    }

    // Theme content as accordion.
    $carousel['content']['product_category_carousel'] = [
      '#theme' => 'alshaya_white_label_accordion',
      '#title' => $accordion_title ?: NULL,
      '#content' => [],
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
  public function getCarousel(ContentEntityInterface $entity) {
    $this->setEntity($entity);
    // By default we don't show any carousel content.
    $carousel['content'] = [];

    $is_accordion = $this->isAccordion();
    if ($is_accordion) {
      $accordion_content = $this->getCarouselAccordion();
      return array_merge($carousel, $accordion_content);
    }

    $slug = $this->getSlug();
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Make carousel title link.
    $carousel_title = [
      'title' => $this->getCarouselTitle(),
      'url' => "/$langcode/$slug/",
    ];

    $settings = $this->configFactory->get('alshaya_acm_product.settings');
    $productCarousel = [
      'sectionTitle' => $carousel_title,
      'itemsPerPage' => $this->getCarouselItemsLimit(),
      'vatText' => $settings->get('vat_text'),
      'slug' => $slug,
    ];

    $carousel['content']['product_category_carousel'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['alshaya-product-category-carousel'],
      ],
      '#attached' => [
        'drupalSettings' => [
          'isRcsCategory' => TRUE,
          'carouselData' => $productCarousel,
          'hp_product_carousel_items' => $settings->get('product_carousel_items_settings.hp_product_carousel_items_number'),
        ],
        'library' => [
          'alshaya_algolia_react/product_category_carousel',
          'alshaya_white_label/product_carousel',
        ],
      ],
    ];

    $view_all_text = $this->getViewAllText();
    if ($view_all_text) {
      $carousel['attributes']['class'][] = 'has-view-all-link';

      $carousel['content']['view_all'] = [
        '#title' => $view_all_text,
        '#type' => 'link',
        '#attributes' => [
          'class' => ['category-carousel-view-all'],
        ],
        '#url' => '',
      ];
    }

    return $carousel;
  }

}
