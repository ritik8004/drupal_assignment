<?php

namespace Drupal\alshaya_furnitures\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\ProductHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Product Info Requested Event Subscriber.
 *
 * @package Drupal\alshaya_furnitures\EventSubscriber
 */
class ProductInfoRequestedEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  private $skuImagesManager;

  /**
   * Product Info Helper.
   *
   * @var \Drupal\alshaya_acm_product\ProductHelper
   */
  protected $productHelper;

  /**
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm_product\ProductHelper $product_helper
   *   Product Info Helper.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   */
  public function __construct(
    SkuImagesManager $sku_images_manager,
    ProductHelper $product_helper,
    SkuManager $sku_manager
  ) {
    $this->skuImagesManager = $sku_images_manager;
    $this->productHelper = $product_helper;
    $this->skuManager = $sku_manager;
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

    $events[ProductInfoRequestedEvent::EVENT_NAME][] = [
      'onProductMediaRequested',
      100,
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
    // PB/WE doesn't require context, we use same title for all context.
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
   * Process title for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processTitle(ProductInfoRequestedEvent $event) {
    $sku = $event->getSku();
    $title = $event->getValue();

    $sku_name = $sku->get('attr_sku_name')->getString();

    if ($sku_name) {
      $title = $sku_name;
    }

    $event->setValue($title);
  }

  /**
   * Subscriber Callback for processing media after media is processed in CORE.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function onProductMediaRequested(ProductInfoRequestedEvent $event) {
    switch ($event->getFieldCode()) {
      case 'media':
        $this->processMedia($event);
        break;
    }
  }

  /**
   * Process media for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processMedia(ProductInfoRequestedEvent $event): void {
    $sku = $event->getSku();

    // We want to add parent images to child in PB/WE.
    if ($sku->bundle() === 'configurable') {
      return;
    }

    $media = $event->getValue();

    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku->getPluginInstance();
    $parent = $plugin->getParentSku($sku);

    if ($parent instanceof SKUInterface) {
      $parent_media = $this->skuImagesManager->getSkuMediaItems($parent);
      $media = array_merge_recursive($media, $parent_media);
    }

    $event->setValue($media);
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
    $return = [];
    $sku_entity = $event->getSku();

    $prod_description = $this->getDescription($sku_entity);
    if ($event->getContext() == 'full') {
      if (!empty($prod_description['description'][0])) {
        $description = $prod_description['description'][0]['value']['#markup'];
        $short_desc = $this->productHelper->createShortDescription($description);

        $return['short_desc'] = [
          'value' => [
            '#markup' => $short_desc['html'],
          ],
        ];
      }
      $event->setValue($return['short_desc']);
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
  protected function getDescription(SKU $sku_entity) {
    $return = [];
    $description = [];
    $static = &drupal_static(__METHOD__, []);

    if (!empty($static[$sku_entity->language()->getId()][$sku_entity->getSku()])) {
      return $static[$sku_entity->language()->getId()][$sku_entity->getSku()];
    }

    $node = $this->skuManager->getDisplayNode($sku_entity);

    $return['description'] = [];
    if ($body = $node->get('body')->getValue()) {
      $description['value'] = [
        '#markup' => $body[0]['value'],
      ];
      $return['description'][] = $description;
    }

    $static[$sku_entity->language()->getId()][$sku_entity->getSku()] = $return;
    return $return;
  }

}
