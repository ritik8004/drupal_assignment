<?php

namespace Drupal\alshaya_hm\EventSubscriber;

use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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

  const ARTICLE_CASTOR_ID_ATTRIBUTE_ID = 999999;

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
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(SkuManager $sku_manager,
                              ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->skuManager = $sku_manager;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
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

      case 'configurable_attributes':
        $this->getConfigurableAttributes($event);
        break;

      case 'product_tree':
        $this->getProductTree($event);
        break;
    }
  }

  /**
   * Process title for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $prod_description = [];
    $event->getValue();
    $search_direction = $sku_entity->getType() == 'configurable' ? 'children' : 'self';

    $description_value = $event->getValue()['description']['#markup'] ?? '';
    if ($concepts = $sku_entity->get('attr_concept')->getValue()) {
      $concepts_markup = [
        '#theme' => 'product_concept_markup',
        '#concepts' => $concepts,
      ];

      $description_value .= render($concepts_markup);
    }

    // Render the wrapper div for composition always so that the same can be
    // filled with data on variant selection.
    // Prepare the description variable.
    $composition = $this->skuManager->fetchProductAttribute($sku_entity, 'attr_composition', $search_direction);
    $composition_markup = [
      '#theme' => 'product_composition_markup',
    ];

    if (!empty($composition)) {
      $composition_markup['#composition']['#markup'] = $composition;
    }

    $description_value .= render($composition_markup);

    $washing_instructions = $sku_entity->get('attr_washing_instructions')
      ->getString();
    $dry_cleaning_instructions = $sku_entity->get('attr_dry_cleaning_instructions')
      ->getString();
    if (!empty($washing_instructions) || !empty($dry_cleaning_instructions)) {
      $description_value .= '<div class="care-instructions-wrapper">';
      $description_value .= '<div class="care-instructions-label">' . $this->t('care instructions') . '</div>';
      if (!empty($washing_instructions)) {
        $description_value .= '<div class="care-instructions-value washing-instructions">' . $washing_instructions . '</div>';
      }
      if (!empty($dry_cleaning_instructions)) {
        $description_value .= '<div class="care-instructions-value dry-cleaning-instructions">' . $dry_cleaning_instructions . '</div>';
      }
      $description_value .= '</div>';
    }

    // Render the wrapper div for article warning always so that the same
    // can be filled with data on variant selection.
    $warning = $this->skuManager->fetchProductAttribute($sku_entity, 'attr_article_warning', $search_direction);
    $warning_markup = [
      '#theme' => 'product_article_warning_markup',
    ];

    if (!empty($warning)) {
      $warning_markup['#warning']['#markup'] = $warning;
    }

    $description_value .= render($warning_markup);

    $description['value'] = [
      '#markup' => $description_value,
    ];

    // If specifications are enabled, prepare the specification variable.
    if ($this->configFactory->get('alshaya_acm.settings')
      ->get('pdp_show_specifications')) {
      $specifications['label'] = [
        '#markup' => $this->t('Specifications'),
      ];

      $specifications['value'] = [
        "#theme" => 'item_list',
        '#items' => [],
      ];

      if ($attr_style_code = $sku_entity->get('attr_style')->getString()) {
        $specifications['value']['#items'][] = $this->t('Style Code: @value', [
          '@value' => $attr_style_code,
        ]);
      }

      if ($attr_color = $sku_entity->get('attr_color')->getString()) {
        $specifications['value']['#items'][] = $this->t('Color: @value', [
          '@value' => $attr_color,
        ]);
      }

      if ($attr_season = $sku_entity->get('attr_season')->getString()) {
        $specifications['value']['#items'][] = $this->t('Season: @value', [
          '@value' => $attr_season,
        ]);
      }

      if ($attr_brand = $sku_entity->get('attr_product_brand')->getString()) {
        $specifications['value']['#items'][] = $this->t('Product brand: @value', [
          '@value' => $attr_brand,
        ]);
      }
    }

    // Add all variables to $build in the sequence in
    // which they should be displayed.
    $prod_description['description'][] = $description;
    if (!empty($specifications)) {
      $prod_description['description'][] = $specifications;
    }

    $event->setValue($prod_description);
  }

  /**
   * Get Configurable Attributes.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function getConfigurableAttributes(ProductInfoRequestedEvent $event) {
    $sku = $event->getSku();
    $configurables = unserialize($sku->get('field_configurable_attributes')->getString());

    // We hard-code attribute name here as it is specific for HnM.
    // Do nothing if this configurable product has article_castor_id as it
    // seems it is not migrated yet to the new configurable per color structure.
    if (isset($configurables['article_castor_id'])) {
      return;
    }

    // If no style code available, return.
    $style = $sku->get('attr_style_code')->getString();
    if (empty($style)) {
      return;
    }

    $skus = $this->entityTypeManager->getStorage('acq_sku')->loadByProperties([
      'attr_style_code' => $style,
    ]);

    $langcode = $sku->language()->getId();
    $configurables = unserialize($sku->get('field_configurable_attributes')->getString());

    foreach ($skus as $variant) {
      if ($variant->language()->getId() != $langcode && $variant->hasTranslation($langcode)) {
        $variant = $variant->getTranslation($langcode);
      }

      if ($variant->bundle() == 'configurable') {
        $variant_configurables = unserialize($variant->get('field_configurable_attributes')->getString());

        foreach ($variant_configurables as $variant_configurable) {
          if (empty($configurables[$variant_configurable['code']])) {
            $configurables[$variant_configurable['code']] = $variant_configurable;
            $configurables[$variant_configurable['code']]['values'] = [];
          }

          foreach ($variant_configurable['values'] as $value) {
            $configurables[$variant_configurable['code']]['values'][$value['value_id']] = $value;
          }
        }
      }
      elseif ($variant->bundle() == 'simple') {
        $attributes = $variant->get('attributes')->getValue();
        $attributes = array_column($attributes, 'value', 'key');
        $colors[$attributes['article_castor_id']] = [
          'label' => $attributes['color_label'],
          'value_id' => $attributes['article_castor_id'],
        ];
      }
    }

    // If we do not find any colors to show, we do nothing.
    if (empty($colors)) {
      return;
    }

    $configurables = [
      'article_castor_id' => [
        'attribute_id' => self::ARTICLE_CASTOR_ID_ATTRIBUTE_ID,
        'code' => 'article_castor_id',
        'label' => t('Article Castor Id'),
        'position' => 0,
        'values' => $colors,
      ],
    ] + $configurables;

    $event->setValue($configurables);
  }

  /**
   * Get Product Tree.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function getProductTree(ProductInfoRequestedEvent $event) {
    $sku = $event->getSku();

    $configurables = unserialize($sku->get('field_configurable_attributes')->getString());

    $configurable_codes = array_column($configurables, 'code', 'code');

    // We hard-code attribute name here as it is specific for HnM.
    // Do nothing if this configurable product has article_castor_id as it
    // seems it is not migrated yet to the new configurable per color structure.
    if (isset($configurable_codes['article_castor_id'])) {
      return;
    }

    // If no style code available, return.
    $style = $sku->get('attr_style_code')->getString();
    if (empty($style)) {
      return;
    }

    $children = $this->entityTypeManager->getStorage('acq_sku')->loadByProperties([
      'attr_style_code' => $style,
      'type' => 'simple',
    ]);

    $variants = [];
    foreach ($children as $child) {
      $variants[$child->getSku()] = $child;
    }

    $tree = [
      'parent' => $sku,
      'products' => $variants,
      'combinations' => [],
      'configurables' => [],
    ];

    // Rest of the processing will be done in alshaya_acm_product.
    $event->setValue($tree);
  }

}
