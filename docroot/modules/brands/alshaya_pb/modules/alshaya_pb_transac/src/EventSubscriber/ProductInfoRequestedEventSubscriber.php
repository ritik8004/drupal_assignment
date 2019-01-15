<?php

namespace Drupal\alshaya_pb_transac\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductInfoRequestedEventSubscriber.
 *
 * @package Drupal\alshaya_pb_transac\EventSubscriber
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
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Manager.
   */
  public function __construct(SkuImagesManager $sku_images_manager) {
    $this->skuImagesManager = $sku_images_manager;
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
    // PB doesn't require context, we use same title for all context.
    switch ($event->getFieldCode()) {
      case 'title':
        $this->processTitle($event);
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

    // We want to add parent images to child in PB.
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

}
