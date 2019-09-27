<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
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
    $skus = [$entity->getSku()];

    if ($entity->bundle() == 'simple') {
      $parents = $entity->getPluginInstance()->getAllParentIds($entity->getSku());
      if (!empty($parents)) {
        $skus = $parents;
      }
    }

    foreach ($skus as $sku) {
      $skuEntity = ($entity->getSku() == $sku)
        ? $entity
        : SKU::loadFromSku($sku);

      if ($skuEntity instanceof SKUInterface) {
        $nid = $entity->getPluginInstance()->getDisplayNodeId($skuEntity);
        if ($nid > 0) {
          Cache::invalidateTags(['node:' . $nid]);
        }
      }
    }

    // Reset all static caches.
    drupal_static_reset();
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

    $parent_skus = $plugin->getParentSku($entity, FALSE);

    if (!empty($parent_skus)) {
      foreach ($parent_skus as $parent_sku) {
        // Update color nodes on save of each child.
        $node = $this->skuManager->getDisplayNode($parent_sku, FALSE);
        if ($node instanceof NodeInterface) {
          $this->skuManager->processColorNodesForConfigurable($node);
          break;
        }
      }
    }
  }

}
