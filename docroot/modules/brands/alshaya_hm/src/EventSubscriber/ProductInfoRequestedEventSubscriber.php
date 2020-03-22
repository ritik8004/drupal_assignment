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
      case 'title':
        $this->processTitle($event);
        break;

      case 'description':
        $this->processDescription($event);
        break;

      case 'short_description':
        $this->processShortDescription($event);
        break;
    }
  }

  /**
   * Process title for SKU based on Beauty products.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processTitle(ProductInfoRequestedEvent $event) {
    $sku = $event->getSku();
    $title = $event->getValue();

    $sku_name = $sku->get('attr_title_name')->getString();

    if (!empty($sku_name)) {
      $title = $sku_name;
    }

    $event->setValue($title);
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
    if ($concepts = $sku_entity->get('attr_concept')->getValue()) {
      $concepts_markup = [
        '#theme' => 'product_concept_markup',
        '#title' => $this->t('concept', [], ['langcode' => $sku_entity->language()->getId()]),
        '#concepts' => $concepts,
      ];
      $description_value .= $this->renderer->renderPlain($concepts_markup);
    }

    // Render the wrapper div for composition always so that the same can be
    // filled with data on variant selection.
    // Prepare the description variable.
    $composition = $this->skuManager->fetchProductAttribute($sku_entity, 'attr_composition', $search_direction);

    if (!empty($composition)) {
      $composition_markup = [
        '#theme' => 'product_composition_markup',
        '#title' => $this->t('composition', [], ['langcode' => $sku_entity->language()->getId()]),
        '#composition' => ['#markup' => $composition],
      ];
      $description_value .= $this->renderer->renderPlain($composition_markup);
    }

    $washing_instructions = $sku_entity->get('attr_washing_instructions')->getString();
    $dry_cleaning_instructions = $sku_entity->get('attr_dry_cleaning_instructions')->getString();
    if (!empty($washing_instructions) || !empty($dry_cleaning_instructions)) {
      $description_value .= '<div class="care-instructions-wrapper">';
      $description_value .= '<div class="care-instructions-label">' . $this->t('care instructions', [], ['langcode' => $sku_entity->language()->getId()]) . '</div>';
      if (!empty($washing_instructions)) {
        $description_value .= '<div class="care-instructions-value washing-instructions">' . $washing_instructions . '</div>';
      }
      if (!empty($dry_cleaning_instructions)) {
        $description_value .= '<div class="care-instructions-value dry-cleaning-instructions">' . $dry_cleaning_instructions . '</div>';
      }
      $description_value .= '</div>';
    }

    if ($article_description = $sku_entity->get('attr_article_description')->getString()) {
      $article_description_markup = [
        '#theme' => 'product_article_description_markup',
        '#title' => $this->t('ARTICLE DESCRIPTION'),
        '#article_description' => ['#markup' => $article_description],
      ];
      $description_value .= $this->renderer->renderPlain($article_description_markup);
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
    if ($function = $sku_entity->get('attr_function')->getValue()) {
      $product_details[] = ['label' => $this->t('Function'), 'data' => $function];
    }

    if ($age_group = $sku_entity->get('attr_age_group')->getValue()) {
      $product_details[] = ['label' => $this->t('Age Group'), 'data' => $age_group];
    }

    if ($style = $sku_entity->get('attr_style')->getValue()) {
      $product_details[] = ['label' => $this->t('Style'), 'data' => $style];
    }

    if ($clothing_style = $sku_entity->get('attr_clothing_style')->getValue()) {
      $product_details[] = ['label' => $this->t('Clothing Style'), 'data' => $clothing_style];
    }

    if ($collar_style = $sku_entity->get('attr_collar_style')->getValue()) {
      $product_details[] = ['label' => $this->t('Collar Style'), 'data' => $collar_style];
    }

    if ($neckline_style = $sku_entity->get('attr_neckline_style')->getValue()) {
      $product_details[] = ['label' => $this->t('Neckline Style'), 'data' => $neckline_style];
    }

    if ($accessories_style = $sku_entity->get('attr_accessories_style')->getValue()) {
      $product_details[] = ['label' => $this->t('`Accessories Style'), 'data' => $accessories_style];
    }

    if ($footwear_style = $sku_entity->get('attr_footwear_style')->getValue()) {
      $product_details[] = ['label' => $this->t('Footwear Style'), 'data' => $footwear_style];
    }

    if ($fit = $sku_entity->get('attr_fit')->getValue()) {
      $product_details[] = ['label' => $this->t('Fit'), 'data' => $fit];
    }

    if ($descriptive_length = $sku_entity->get('attr_descriptive_length')->getValue()) {
      $product_details[] = ['label' => $this->t('Descriptive Length'), 'data' => $descriptive_length];
    }

    if ($garment_length = $sku_entity->get('attr_garment_length')->getValue()) {
      $product_details[] = ['label' => $this->t('Garment Length'), 'data' => $garment_length];
    }

    if ($sleeve_length = $sku_entity->get('attr_sleeve_length')->getValue()) {
      $product_details[] = ['label' => $this->t('Sleeve Length'), 'data' => $sleeve_length];
    }

    if ($waist_rise = $sku_entity->get('attr_waist_rise')->getValue()) {
      $product_details[] = ['label' => $this->t('Waist Rise'), 'data' => $waist_rise];
    }

    if ($heel_height = $sku_entity->get('attr_heel_height')->getValue()) {
      $product_details[] = ['label' => $this->t('Heel Height'), 'data' => $heel_height];
    }

    if ($measurements_in_cm = $sku_entity->get('attr_measurements_in_cm')->getValue()) {
      $product_details[] = ['label' => $this->t('Measurments in cm'), 'data' => $measurements_in_cm];
    }

    if ($fragrance_name = $sku_entity->get('attr_fragrance_name')->getValue()) {
      $product_details[] = ['label' => $this->t('Fragrance Name'), 'data' => $fragrance_name];
    }

    if ($textual_print = $sku_entity->get('attr_textual_print')->getValue()) {
      $product_details[] = ['label' => $this->t('Textual print'), 'data' => $textual_print];
    }

    if ($article_license_company = $sku_entity->get('attr_article_license_company')->getValue()) {
      $product_details[] = ['label' => $this->t('Article License Company'), 'data' => $article_license_company];
    }

    if ($article_license_item = $sku_entity->get('attr_article_license_item')->getValue()) {
      $product_details[] = ['label' => $this->t('Ariticle Lisence Item'), 'data' => $article_license_item];
    }

    $details_overlay_markup = [
      '#theme' => 'product_details_overlay_markup',
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
