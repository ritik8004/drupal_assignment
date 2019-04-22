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

    // Get all parent skus this sku.
    $parent_skus = $this->skuManager->getParentSkus([$entity->getSku()]);

    $cache_tags = [];

    // If there any parent sku available.
    if (!empty($parent_skus)) {
      foreach ($parent_skus as $sku_id => $parent_sku) {
        // Invalidate caches for all parent skus here.
        $cache_tags = Cache::mergeTags($cache_tags, ['acq_sku:' . $sku_id]);
        // We also invalidate caches for node here.
        $node = $this->skuManager->getDisplayNode($parent_sku);
        if ($node instanceof NodeInterface) {
          $cache_tags = Cache::mergeTags($cache_tags, $node->getCacheTagsToInvalidate());
        }
      }
    }
    else {
      // If no parent sku, means this will either be a configurable sku or just
      // a simple sku directly attached to node. Just invalidate the display
      // node cache in this case.
      $node = $this->skuManager->getDisplayNode($entity);
      if ($node instanceof NodeInterface) {
        $cache_tags = Cache::mergeTags($cache_tags, $node->getCacheTagsToInvalidate());
      }
    }

    // Invalidate cache.
    if (!empty($cache_tags)) {
      Cache::invalidateTags($cache_tags);
    }

  }

  /**
   * Subscriber Callback for the event to process color nodes.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductUpdatedProcessColor(ProductUpdatedEvent $event) {
    // Do nothing when listing display mode is not non-aggregated.
    if (!$this->skuManager->isListingModeNonAggregated()) {
      return;
    }

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
