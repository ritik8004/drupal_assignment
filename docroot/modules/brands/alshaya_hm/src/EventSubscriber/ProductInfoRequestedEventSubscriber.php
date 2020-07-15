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
 * Class ProductInfoRequestedEventSubscriber.
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
    switch ($context) {
      case 'plp':
        $plp_attributes = $config->get('plp');
        $plp_label = [];
        foreach ($plp_attributes as $attribute) {
          if ($sku->get($attribute)->getString()) {
            $plp_label['content'] = $sku->get($attribute)
              ->getString();
            $plp_label['class'] = $attribute;
            break;
          }
        }
        $event->setValue($plp_label);
        break;

      case 'pdp':
        $pdp_attributes = $config->get('pdp');
        $pdp_labels = [];
        foreach ($pdp_attributes as $attribute) {
          if ($sku->get($attribute)->getString()) {
            $pdp_labels[] = [
              'content' => $sku->get($attribute)
                ->getString(),
              'class' => $attribute,
            ];
          }
        }
        $event->setValue($pdp_labels);
        break;
    }
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
    $event->getValue($prod_description['short_desc']);
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
    $static = &drupal_static(__METHOD__, []);

    if (!empty($static[$sku_entity->language()->getId()][$sku_entity->getSku()])) {
      return $static[$sku_entity->language()->getId()][$sku_entity->getSku()];
    }

    $search_direction = $sku_entity->getType() == 'configurable' ? 'children' : 'self';

    $description_value = '';
    if ($body = $sku_entity->get('attr_description')->getValue()) {
      $description_value = '<div class="description-first">';
      $description_value .= $body[0]['value'];
      $description_value .= '</div>';
    }

    $description_value .= '<div class="description-details">';
    if ($title_name = $sku_entity->get('attr_title_name')->getString()) {
      $title_name_markup = [
        '#theme' => 'product_title_name_markup',
        '#title' => $this->t('TITLE NAME'),
        '#title_name' => $title_name,
      ];
      $description_value .= $this->renderer->renderPlain($title_name_markup);
    }

    if ($product_designer_collection = $sku_entity->get('attr_product_designer_collection')->getValue()) {
      $designer_collection_markup = [
        '#theme' => 'pdp_main_attributes_markup',
        '#properties' => ['title' => $this->t('DESIGNER COLLECTION'), 'value' => $product_designer_collection],
      ];
      $description_value .= $this->renderer->renderPlain($designer_collection_markup);
    }

    if ($product_collection = $sku_entity->get('attr_product_collection')->getValue()) {
      $collection_markup = [
        '#theme' => 'pdp_main_attributes_markup',
        '#properties' => ['title' => $this->t('COLLECTION'), 'value' => $product_collection],
      ];
      $description_value .= $this->renderer->renderPlain($collection_markup);
    }

    if ($product_environment = $sku_entity->get('attr_product_environment')->getValue()) {
      $environment_markup = [
        '#theme' => 'pdp_main_attributes_markup',
        '#properties' => ['title' => $this->t('ENVIRONMENT'), 'value' => $product_environment],
      ];
      $description_value .= $this->renderer->renderPlain($environment_markup);
    }

    if ($product_quality = $sku_entity->get('attr_product_quality')->getValue()) {
      $quality_markup = [
        '#theme' => 'pdp_main_attributes_markup',
        '#properties' => ['title' => $this->t('QUALITY'), 'value' => $product_quality],
      ];
      $description_value .= $this->renderer->renderPlain($quality_markup);
    }

    if ($fit = $sku_entity->get('attr_fit')->getValue()) {
      $fit_markup = [
        '#theme' => 'pdp_main_attributes_markup',
        '#properties' => ['title' => $this->t('FIT'), 'value' => $fit],
      ];
      $description_value .= $this->renderer->renderPlain($fit_markup);
    }

    if ($article_description = $sku_entity->get('attr_article_description')->getValue()) {
      $article_description_markup = [
        '#theme' => 'pdp_main_attributes_markup',
        '#properties' => ['title' => $this->t('ARTICLE DESCRIPTION'), 'value' => $article_description],
      ];
      $description_value .= $this->renderer->renderPlain($article_description_markup);
    }

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
        '#title' => $this->t('ART NO'),
        '#item_code' => $sku_entity->getSku(),
      ];
      $description_value .= $this->renderer->renderPlain($item_code);
    }

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

    // To display detailed description in overlay section.
    $product_details = [];
    if ($product_designer_collection = $sku_entity->get('attr_product_designer_collection')->getValue()) {
      $product_details[] = ['label' => $this->t('DESIGNER COLLECTION'), 'data' => $product_designer_collection];
    }

    if ($concept = $sku_entity->get('attr_concept')->getValue()) {
      $product_details[] = ['label' => $this->t('CONCEPT'), 'data' => $concept];
    }

    if ($product_collection = $sku_entity->get('attr_product_collection')->getValue()) {
      $product_details[] = ['label' => $this->t('COLLECTION'), 'data' => $product_collection];
    }

    if ($product_environment = $sku_entity->get('attr_product_environment')->getValue()) {
      $product_details[] = ['label' => $this->t('ENVIRONMENT'), 'data' => $product_environment];
    }

    if ($product_quality = $sku_entity->get('attr_product_quality')->getValue()) {
      $product_details[] = ['label' => $this->t('QUALITY'), 'data' => $product_quality];
    }

    if ($product_feature = $sku_entity->get('attr_product_feature')->getValue()) {
      $product_details[] = ['label' => $this->t('FEATURE'), 'data' => $product_feature];
    }

    if ($function = $sku_entity->get('attr_function')->getValue()) {
      $product_details[] = ['label' => $this->t('FUNCTION'), 'data' => $function];
    }

    if ($washing_instructions = $sku_entity->get('attr_washing_instructions')->getValue()) {
      $product_details[] = ['label' => $this->t('WASHING INSTRUCTION'), 'data' => $washing_instructions];
    }

    if ($dry_cleaning_instructions = $sku_entity->get('attr_dry_cleaning_instructions')->getValue()) {
      $product_details[] = ['label' => $this->t('DRY CLEAN INSTRUCTION'), 'data' => $dry_cleaning_instructions];
    }

    if ($style = $sku_entity->get('attr_style')->getValue()) {
      $product_details[] = ['label' => $this->t('STYLE'), 'data' => $style];
    }

    if ($clothing_style = $sku_entity->get('attr_clothing_style')->getValue()) {
      $product_details[] = ['label' => $this->t('CLOTHING STYLE'), 'data' => $clothing_style];
    }

    if ($collar_style = $sku_entity->get('attr_collar_style')->getValue()) {
      $product_details[] = ['label' => $this->t('COLLAR STYLE'), 'data' => $collar_style];
    }

    if ($neckline_style = $sku_entity->get('attr_neckline_style')->getValue()) {
      $product_details[] = ['label' => $this->t('NECKLINE STYLE'), 'data' => $neckline_style];
    }

    if ($accessories_style = $sku_entity->get('attr_accessories_style')->getValue()) {
      $product_details[] = ['label' => $this->t('ACCESSORIES STYLE'), 'data' => $accessories_style];
    }

    if ($footwear_style = $sku_entity->get('attr_footwear_style')->getValue()) {
      $product_details[] = ['label' => $this->t('FOOTWEAR STYLE'), 'data' => $footwear_style];
    }

    if ($fit = $sku_entity->get('attr_fit')->getValue()) {
      $product_details[] = ['label' => $this->t('FIT'), 'data' => $fit];
    }

    if ($descriptive_length = $sku_entity->get('attr_descriptive_length')->getValue()) {
      $product_details[] = ['label' => $this->t('DESCRIPTIVE LENGTH'), 'data' => $descriptive_length];
    }

    if ($garment_length = $sku_entity->get('attr_garment_length')->getValue()) {
      $product_details[] = ['label' => $this->t('GARMENT LENGTH'), 'data' => $garment_length];
    }

    if ($sleeve_length = $sku_entity->get('attr_sleeve_length')->getValue()) {
      $product_details[] = ['label' => $this->t('SLEEVE LENGTH'), 'data' => $sleeve_length];
    }

    if ($waist_rise = $sku_entity->get('attr_waist_rise')->getValue()) {
      $product_details[] = ['label' => $this->t('WAIST RISE'), 'data' => $waist_rise];
    }

    if ($heel_height = $sku_entity->get('attr_heel_height')->getValue()) {
      $product_details[] = ['label' => $this->t('HEEL HEIGHT'), 'data' => $heel_height];
    }

    if ($measurements_in_cm = $sku_entity->get('attr_measurements_in_cm')->getValue()) {
      $product_details[] = ['label' => $this->t('MEASURMENTS IN CM'), 'data' => $measurements_in_cm];
    }

    if ($sku_entity->hasField('color_name') && $color_name = $sku_entity->get('attr_color_name')->getValue()) {
      $product_details[] = ['label' => $this->t('COLOR NAME'), 'data' => $color_name];
    }

    if ($fragrance_name = $sku_entity->get('attr_fragrance_name')->getValue()) {
      $product_details[] = ['label' => $this->t('FRAGRANCE NAME'), 'data' => $fragrance_name];
    }

    if ($article_fragrance_description = $sku_entity->get('attr_article_fragrance_description')->getValue()) {
      $product_details[] = ['label' => $this->t('FRAGRANCE DESCRIPTION'), 'data' => $article_fragrance_description];
    }

    if ($article_pattern = $sku_entity->get('attr_article_pattern')->getValue()) {
      $product_details[] = ['label' => $this->t('PATTERN'), 'data' => $article_pattern];
    }

    if ($article_visual_description = $sku_entity->get('attr_article_visual_description')->getValue()) {
      $product_details[] = ['label' => $this->t('VISUAL DESCRIPTION'), 'data' => $article_visual_description];
    }

    if ($textual_print = $sku_entity->get('attr_textual_print')->getValue()) {
      $product_details[] = ['label' => $this->t('TEXTUAL PRINT'), 'data' => $textual_print];
    }

    if ($article_license_company = $sku_entity->get('attr_article_license_company')->getValue()) {
      $product_details[] = ['label' => $this->t('LICENSE COMPANY'), 'data' => $article_license_company];
    }

    if ($article_license_item = $sku_entity->get('attr_article_license_item')->getValue()) {
      $product_details[] = ['label' => $this->t('LICENSE ITEM'), 'data' => $article_license_item];
    }

    // Render the wrapper div for article warning always so that the same
    // can be filled with data on variant selection.
    $composition = $this->skuManager->fetchProductAttribute($sku_entity, 'attr_composition', $search_direction);
    if (!empty($composition)) {
      $product_details[] = ['label' => $this->t('COMPOSITION'), 'composition' => ['#markup' => $composition]];
    }

    $details_overlay_markup = [
      '#theme' => 'pdp_additional_attribute_overlay',
      '#properties' => $product_details,
    ];

    $prod_description[] = $details_overlay_markup;

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

}
