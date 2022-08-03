<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
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
   * The source entity for the carousel.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * The carousel main category id.
   *
   * @var int
   */
  protected $categoryId;

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
   * Sets the source entity for the carousel from which we prepare data.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Source entity for the carousel.
   */
  protected function setEntity(ContentEntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * Gets the category id for the carousel source term.
   */
  private function loadCategoryId() {
    $category_id = $this->entity->get('field_category_carousel')->getString();
    $this->categoryId = !empty($category_id) ? (int) $category_id : NULL;
  }

  /**
   * Checks if the carousel is displayed as an accordion or not.
   *
   * @return bool
   *   Returns true, if carousel is displayed as an accordion else false.
   */
  protected function isAccordion() {
    return (bool) $this->entity->get('field_use_as_accordion')->getString();
  }

  /**
   * Gets the limit of the items to show in the carousel.
   *
   * @return int
   *   The number of items in the carousel.
   */
  protected function getCarouselItemsLimit() {
    return (int) $this->entity->get('field_category_carousel_limit')->getString();
  }

  /**
   * Gets the view all text.
   *
   * @return string
   *   The text which is shown in the view all button.
   */
  protected function getViewAllText() {
    return $this->entity->get('field_view_all_text')->getString();
  }

  /**
   * Gets the title of the carousel.
   *
   * @return string
   *   The title of the carousel.
   */
  protected function getCarouselTitle() {
    return $this->entity->get('field_category_carousel_title')->getString();
  }

  /**
   * Create and returns the render array for category carousel accordion.
   *
   * @return array
   *   The render array for carousel accordion.
   */
  private function getCarouselAccordion() {
    $carousel = [];
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($this->categoryId);
    // If given category not available.
    if (!$term instanceof TermInterface) {
      return [];
    }

    $carousel_title = $this->getCarouselTitle();
    if (empty($carousel_title)) {
      $carousel_title = $term->label();
    }

    // Create accordion title link.
    $accordion_title = [
      '#type' => 'link',
      '#title' => $carousel_title,
      '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $this->categoryId]),
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
        '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $this->categoryId]),
      ];
    }

    // Theme content as accordion.
    $carousel['content']['product_category_carousel'] = [
      '#theme' => 'alshaya_white_label_accordion',
      '#title' => $accordion_title ?: NULL,
      '#content' => alshaya_acm_product_category_child_terms($this->categoryId),
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
    $carousel = [];
    $this->setEntity($entity);
    $this->loadCategoryId();
    // By default we don't show any carousel content.
    $carousel['content'] = [];

    // If category is null/empty, no need to process further.
    if (empty($this->categoryId)) {
      return $carousel;
    }

    $is_accordion = $this->isAccordion();
    if ($is_accordion) {
      $accordion_content = $this->getCarouselAccordion();
      return array_merge($carousel, $accordion_content);
    }

    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $productCarousel = $this->productCategoryPage->getCurrentSelectedCategory($langcode, $this->categoryId);
    $settings = $this->configFactory->get('alshaya_acm_product.settings');

    // Make carousel title link.
    $carousel_title = [
      'title' => $this->getCarouselTitle(),
      'url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $this->categoryId])->toString(),
    ];

    $productCarousel = array_merge($productCarousel, [
      'sectionTitle' => $carousel_title,
      'itemsPerPage' => $this->getCarouselItemsLimit(),
      'vatText' => $settings->get('vat_text'),
    ]);

    $carousel['content']['product_category_carousel'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['alshaya-product-category-carousel'],
        'data-pcc-id' => $this->categoryId,
      ],
      '#attached' => [
        'drupalSettings' => [
          'alshayaProductCarousel' => [
            $this->categoryId => $productCarousel,
          ],
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
        '#url' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $this->categoryId]),
      ];
    }

    return $carousel;
  }

}
