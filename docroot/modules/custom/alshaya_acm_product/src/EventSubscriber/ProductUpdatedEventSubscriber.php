<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Drupal\node\NodeInterface;
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

    // Reset all static caches.
    drupal_static_reset();

    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $entity->getPluginInstance();

    // @TODO: Make this smart in CORE-3443.
    if ($parent = $plugin->getParentSku($entity)) {
      Cache::invalidateTags($parent->getCacheTagsToInvalidate());
    }

    // We also invalidate caches for node here.
    $node = $this->skuManager->getDisplayNode($parent);
    if ($node instanceof NodeInterface) {
      Cache::invalidateTags($node->getCacheTagsToInvalidate());
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
