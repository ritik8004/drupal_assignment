<?php

namespace Drupal\alshaya_hm\EventSubscriber;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Contains Product Info Requested Event Subscriber methods.
 *
 * @package Drupal\alshaya_hm\EventSubscriber
 */
class ProductInfoRequestedEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(
    SkuManager $sku_manager,
    ConfigFactoryInterface $config_factory,
    RendererInterface $renderer
  ) {
    $this->skuManager = $sku_manager;
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductInfoRequestedEvent::EVENT_NAME][] = [
      'onProductInfoRequested',
      800,
    ];

    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function onProductInfoRequested(ProductInfoRequestedEvent $event) {
    switch ($event->getFieldCode()) {
      case 'description':
        $this->processDescription($event);
        break;

      case 'short_description':
        $this->processShortDescription($event);
        break;

      case 'collection_labels':
        $this->processCollectionLabels($event);
        break;
    }
  }

  /**
   * Get collection labels from sku.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processCollectionLabels(ProductInfoRequestedEvent $event) {
    $sku = $event->getSku();
    $context = $event->getContext();
    $config = $this->configFactory->get('alshaya_hm.label_order.settings');
    $collection_attributes = $config->get($context);
    $labels = [];

    foreach ($collection_attributes as $attribute) {
      if ($attribute_value = $sku->get($attribute)->getString()) {
        $labels[$attribute] = $attribute_value;

        if ($context === 'plp') {
          // In plp we only display a single label.
          break;
        }
      }
    }

    $event->setValue($labels);
  }

  /**
   * Process description for SKU.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $prod_description = $this->getDescription($sku_entity);
    $event->setValue($prod_description['description']);
  }

  /**
   * Process short descriptions for SKU.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processShortDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $prod_description = $this->getDescription($sku_entity);
    $event->setValue($prod_description['short_desc']);
  }

  /**
   * Prepare description and short description array for given sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   The sku entity.
   *
   * @return array
   *   Return array of description and short description.
   */
  protected function getDescription(SKU $sku_entity) {
    $description = [];
    $prod_description = [];
    $return = [];
    $static = &drupal_static(__METHOD__, []);

    if (!empty($static[$sku_entity->language()->getId()][$sku_entity->getSku()])) {
      return $static[$sku_entity->language()->getId()][$sku_entity->getSku()];
    }

    $description_value = '';
    if ($body = $sku_entity->get('attr_description')->getValue()) {
      $description_value = '<div class="description-first">';
      $description_value .= $body[0]['value'];
      $description_value .= '</div>';
    }

    $description_value .= '<div class="description-details">';
    $description_value .= $this->displayAttributesOnMain($sku_entity);
    $description_value .= '</div>';

    $description['value'] = [
      '#markup' => $description_value,
    ];

    // Add all variables to $build in the sequence in
    // which they should be displayed.
    $prod_description[] = $description;

    // If specifications are enabled, prepare the specification variable.
    if ($this->configFactory->get('alshaya_acm.settings')->get('pdp_show_specifications')) {
      $specifications = [
        'label' => [
          '#markup' => $this->t('Specifications', [], ['langcode' => $sku_entity->language()->getId()]),
        ],
        'value' => [
          "#theme" => 'item_list',
          '#items' => [],
        ],
      ];

      if ($attr_style_code = $sku_entity->get('attr_style')->getString()) {
        $specifications['value']['#items'][] = $this->t('Style Code: @value', [
          '@value' => $attr_style_code,
        ], ['langcode' => $sku_entity->language()->getId()]);
      }

      if ($attr_color = $sku_entity->get('attr_color')->getString()) {
        $specifications['value']['#items'][] = $this->t('Color: @value', [
          '@value' => $attr_color,
        ], ['langcode' => $sku_entity->language()->getId()]);
      }

      if ($attr_season = $sku_entity->get('attr_season')->getString()) {
        $specifications['value']['#items'][] = $this->t('Season: @value', [
          '@value' => $attr_season,
        ], ['langcode' => $sku_entity->language()->getId()]);
      }

      if ($attr_brand = $sku_entity->get('attr_product_brand')->getString()) {
        $specifications['value']['#items'][] = $this->t('Product brand: @value', [
          '@value' => $attr_brand,
        ], ['langcode' => $sku_entity->language()->getId()]);
      }

      $prod_description[] = $specifications;
    }

    $product_details = $this->displayAttributesOnOverlay($sku_entity);
    if (!empty($product_details)) {
      $details_overlay_markup = [
        '#theme' => 'pdp_additional_attribute_overlay',
        '#properties' => $product_details,
      ];

      $prod_description[] = $details_overlay_markup;
    }

    // Add all variables to $build in the sequence in
    // which they should be displayed.
    // Check comments in MMCPA-218 for sequence requirements.
    $return['description'] = $prod_description;

    // $short_desc contains the description that should be
    // displayed before 'Read More'.
    $short_desc = $prod_description[0];
    // If short description not available, check other consecutive fields.
    if (empty($short_desc['value']['#markup'])) {
      foreach ($description as $short_description) {
        // If value is available in next field, then
        // use it and no need to process further.
        if (!empty($short_description['value']['#markup'])) {
          $short_desc = $short_description;
          break;
        }
      }
    }
    $return['short_desc'] = $short_desc;

    $static[$sku_entity->language()->getId()][$sku_entity->getSku()] = $return;
    return $return;
  }

  /**
   * Prepare description array for given sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   The sku entity.
   *
   * @return array
   *   Return array of values for attributes to show in main section.
   */
  private function displayAttributesOnMain(SKU $sku_entity) {
    $description_value = '';
    if ($title_name = $sku_entity->get('attr_title_name')->getString()) {
      $title_name_markup = [
        '#theme' => 'product_title_name_markup',
        '#title' => $this->t('TITLE NAME'),
        '#title_name' => $title_name,
      ];
      $description_value .= $this->renderer->renderPlain($title_name_markup);
    }

    $list_of_attributes = [
      'attr_fit' => $this->t('FIT'),
      'attr_article_description' => $this->t('ARTICLE DESCRIPTION'),
    ];

    $properties = [];
    foreach ($list_of_attributes as $key => $title) {
      if ($value = $sku_entity->get($key)->getValue()) {
        $properties[] = [
          'title' => $title,
          'data' => $value,
        ];
      }
    }

    if (!empty($properties)) {
      $pdp_main_attributes_markup = [
        '#theme' => 'pdp_main_attributes_markup',
        '#properties' => $properties,
      ];
      $description_value .= $this->renderer->renderPlain($pdp_main_attributes_markup);
    }

    $search_direction = $sku_entity->getType() == 'configurable' ? 'children' : 'self';
    // Render the wrapper div for article warning always so that the same
    // can be filled with data on variant selection.
    $warning = $this->skuManager->fetchProductAttribute($sku_entity, 'attr_article_warning', $search_direction);

    if (!empty($warning)) {
      $warning_markup = [
        '#theme' => 'product_article_warning_markup',
        '#title' => $this->t('safety warning', [], ['langcode' => $sku_entity->language()->getId()]),
        '#warning' => ['#markup' => $warning],
      ];
      $description_value .= $this->renderer->renderPlain($warning_markup);
    }

    // Render SKU id for magazine layou on PDP.
    if (!empty($sku_entity->getSku())) {
      $item_code = [
        '#theme' => 'product_item_code_markup',
        '#title' => self::getLabelFromKey('item_code_label'),
        '#item_code' => $sku_entity->getSku(),
      ];
      $description_value .= $this->renderer->renderPlain($item_code);
    }

    return $description_value;
  }

  /**
   * Provides the label for a given key.
   *
   * @param string $key
   *   The key whose label is required.
   *
   * @return string|null
   *   Return label based on the key or null if key not found.
   */
  public static function getLabelFromKey($key) {
    $mapping = [
      'item_code_label' => t('ART NO'),
    ];

    return $mapping[$key] ?? NULL;
  }

  /**
   * Prepare descriptions for given sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   The sku entity.
   *
   * @return array
   *   Return array of values for attributes to show in overlay section.
   */
  private function displayAttributesOnOverlay(SKU $sku_entity) {
    $product_details = [];
    $list_of_attributes = [
      'attr_product_designer_collection' => $this->t('DESIGNER COLLECTION'),
      'attr_concept' => $this->t('CONCEPT'),
      'attr_product_collection' => $this->t('COLLECTION'),
      'attr_product_environment' => $this->t('ENVIRONMENT'),
      'attr_product_quality' => $this->t('QUALITY'),
      'attr_product_feature' => $this->t('FEATURE'),
      'attr_function' => $this->t('FUNCTION'),
      'attr_washing_instructions' => $this->t('WASHING INSTRUCTION'),
      'attr_dry_cleaning_instructions' => $this->t('DRY CLEAN INSTRUCTION'),
      'attr_style' => $this->t('STYLE'),
      'attr_clothing_style' => $this->t('CLOTHING STYLE'),
      'attr_collar_style' => $this->t('COLLAR STYLE'),
      'attr_neckline_style' => $this->t('NECKLINE STYLE'),
      'attr_accessories_style' => $this->t('ACCESSORIES STYLE'),
      'attr_footwear_style' => $this->t('FOOTWEAR STYLE'),
      'attr_fit' => $this->t('FIT'),
      'attr_descriptive_length' => $this->t('DESCRIPTIVE LENGTH'),
      'attr_garment_length' => $this->t('GARMENT LENGTH'),
      'attr_sleeve_length' => $this->t('SLEEVE LENGTH'),
      'attr_waist_rise' => $this->t('WAIST RISE'),
      'attr_heel_height' => $this->t('HEEL HEIGHT'),
      'attr_measurements_in_cm' => $this->t('MEASURMENTS IN CM'),
      'attr_color_name' => $this->t('COLOR NAME'),
      'attr_fragrance_name' => $this->t('FRAGRANCE NAME'),
      'attr_article_fragrance_description' => $this->t('FRAGRANCE DESCRIPTION'),
      'attr_article_pattern' => $this->t('PATTERN'),
      'attr_article_visual_description' => $this->t('VISUAL DESCRIPTION'),
      'attr_textual_print' => $this->t('TEXTUAL PRINT'),
      'attr_article_license_company' => $this->t('LICENSE COMPANY'),
      'attr_article_license_item' => $this->t('LICENSE ITEM'),
    ];

    foreach ($list_of_attributes as $key => $title) {
      if ($attr_val = $sku_entity->get($key)->getValue()) {
        $product_details[] = [
          'label' => $title,
          'data' => $attr_val,
        ];
      }
    }

    $search_direction = $sku_entity->getType() == 'configurable' ? 'children' : 'self';
    $composition = $this->skuManager->fetchProductAttribute($sku_entity, 'attr_composition', $search_direction);
    if (!empty($composition)) {
      $product_details[] = [
        'label' => $this->t('COMPOSITION'),
        'composition' => ['#markup' => $composition],
      ];
    }

    return $product_details;
  }

}
