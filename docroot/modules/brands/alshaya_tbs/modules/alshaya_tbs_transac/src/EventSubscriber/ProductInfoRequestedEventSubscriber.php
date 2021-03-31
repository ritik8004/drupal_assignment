<?php

namespace Drupal\alshaya_tbs_transac\EventSubscriber;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\ProductHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Contains Product Info Requested Event Subscriber methods.
 *
 * @package Drupal\alshaya_tbs_transac\EventSubscriber
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
   * Product helper service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $productHelper;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\alshaya_acm_product\ProductHelper $product_helper
   *   Product helper service object.
   */
  public function __construct(
    SkuManager $sku_manager,
    ConfigFactoryInterface $config_factory,
    ProductHelper $product_helper
  ) {
    $this->skuManager = $sku_manager;
    $this->configFactory = $config_factory;
    $this->productHelper = $product_helper;
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
    $description = $this->getDescription($sku_entity);
    $event->setValue($description['description']);
  }

  /**
   * Process short descriptions for SKU.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processShortDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $description = $this->getDescription($sku_entity);

    if ($event->getContext() == 'full') {
      $short_desc = $this->productHelper->createShortDescription($description['short_desc']['value']['#markup']);
      $description['short_desc']['value']['#markup'] = $short_desc['html'];
      $event->setValue($description['short_desc']);
    }
    else {
      $event->setValue($description['short_desc']);
    }
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
  private function getDescription(SKU $sku_entity) {
    $static = &drupal_static(__METHOD__, []);

    if (!empty($static[$sku_entity->language()->getId()][$sku_entity->getSku()])) {
      return $static[$sku_entity->language()->getId()][$sku_entity->getSku()];
    }

    if ($attr_at_glance = $sku_entity->get('attr_at_glance')->getString()) {
      $description[] = [
        'label' => [
          '#markup' => $this->t('At a glance', [], ['langcode' => $sku_entity->language()->getId()]),
        ],
        'value' => ['#markup' => $attr_at_glance],
      ];
    }

    // Prepare $description variable.
    $description_value = '';
    if ($body = $sku_entity->get('attr_description')->getValue()) {
      $description_value = $body[0]['value'];
    }

    if ($bullet_points = $sku_entity->get('attr_bullet_points')->getString()) {
      $description_value .= '<div class="bullet-points-wrapper">';
      $description_value .= $bullet_points;
      $description_value .= '</div>';
    }

    $description[] = [
      'label' => [
        '#markup' => $this->t('Features and benefits', [], ['langcode' => $sku_entity->language()->getId()]),
      ],
      'value' => ['#markup' => $description_value],
    ];

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
      $description[] = $specifications;
    }

    if ($in_box = $sku_entity->get('attr_whats_in_the_box')->getString()) {
      $description[] = [
        'label' => [
          '#markup' => $this->t("What's In The Box", [], ['langcode' => $sku_entity->language()->getId()]),
        ],
        'value' => ['#markup' => $in_box],
      ];
    }

    // Add all variables to $build in the sequence in
    // which they should be displayed.
    // Check comments in MMCPA-218 for sequence requirements.
    $return['description'] = $description;

    // $short_desc contains the description that should be
    // displayed before 'Read More'.
    $short_desc = $description[0];
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
