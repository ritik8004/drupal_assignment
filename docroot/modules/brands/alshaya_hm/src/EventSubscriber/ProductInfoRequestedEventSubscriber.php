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
