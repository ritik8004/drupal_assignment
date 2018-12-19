<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductUpdatedEventSubscriber.
 *
 * @package Drupal\alshaya_acm_product\EventSubscriber
 */
class ProductUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   */
  public function __construct(SkuManager $sku_manager) {
    $this->skuManager = $sku_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::EVENT_NAME][] = ['onProductUpdated', 999];
    $events[ProductUpdatedEvent::EVENT_NAME][] = ['onProductUpdatedProcessColor', 500];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductUpdated(ProductUpdatedEvent $event) {
    $entity = $event->getSku();

    $this->skuManager->clearProductCachedData($entity);

    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $entity->getPluginInstance();

    if ($parent = $plugin->getParentSku($entity)) {
      // Clear cached data for configurable products.
      $this->skuManager->clearProductCachedData($parent);

      // Reset the sku static cache.
      drupal_static_reset('loadFromSku');

      // @TODO: Make this smart in CORE-3443.
      Cache::invalidateTags($parent->getCacheTagsToInvalidate());
    }
  }

  /**
   * Subscriber Callback for the event to process color nodes.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductUpdatedProcessColor(ProductUpdatedEvent $event) {
    $entity = $event->getSku();

    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $entity->getPluginInstance();

    if ($parent = $plugin->getParentSku($entity)) {
      // Update color nodes on save of each child.
      $node = $this->skuManager->getDisplayNode($parent, FALSE);
      if ($node instanceof NodeInterface) {
        $this->skuManager->processColorNodesForConfigurable($node);
      }
    }
  }

}
