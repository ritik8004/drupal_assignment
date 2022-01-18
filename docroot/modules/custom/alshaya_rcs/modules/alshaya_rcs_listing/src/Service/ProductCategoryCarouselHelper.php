<?php

namespace Drupal\alshaya_rcs_listing\Service;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryCarouselHelper as ProductCategoryCarouselHelperOriginal;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryPage;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;

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
    $slug = $this->getSlug();

    // Create accordion title link.
    $accordion_title = [
      '#type' => 'link',
      '#title' => $carousel_title,
      '#url' => $slug,
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
        '#url' => $slug,
      ];
    }

    // Theme content as accordion.
    $carousel['content']['product_category_carousel'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['alshaya-product-category-carousel-accordion'],
        'data-slug' => $slug,
        'data-title' => $accordion_title ?? NULL,
        'data-view-all' => json_encode([
          'text' => $this->getViewAllText(),
          'class' => 'category-accordion-view-all',
        ]),
      ],
      '#content' => '#rcs.carousel.accordion#',
      '#cache' => [
        'tags' => [
          ProductCategoryTree::CACHE_TAG,
        ],
      ],
      '#attached' => [
        'drupalSettings' => [
          'alshayaProductCarousel' => [
            $slug => $carousel_title,
          ],
        ],
        'library' => [
          'alshaya_white_label/product-category-accordion',
          'alshaya_algolia_react/product_category_carousel_v2',
          'alshaya_white_label/product_carousel',
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
    $category_url = "/$langcode/$slug/";

    // Make carousel title link.
    $carousel_title = [
      'title' => $this->getCarouselTitle(),
      'url' => $category_url,
    ];

    $settings = $this->configFactory->get('alshaya_acm_product.settings');

    $carousel['content']['product_category_carousel'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['alshaya-product-category-carousel'],
        'data-slug' => $slug,
      ],
      '#attached' => [
        'drupalSettings' => [
          'alshayaProductCarousel' => [
            'itemsPerPage' => $this->getCarouselItemsLimit(),
            'vatText' => $settings->get('vat_text'),
            $slug => $carousel_title,
          ],
          'hp_product_carousel_items' => $settings->get('product_carousel_items_settings.hp_product_carousel_items_number'),
        ],
        'library' => [
          'alshaya_algolia_react/product_category_carousel_v2',
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
        '#url' => Url::fromUserInput($category_url),
      ];
    }

    return $carousel;
  }

}
