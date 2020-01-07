<?php

namespace Drupal\alshaya_color_split\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_color_split\AlshayaColorSplitManager;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\NodeInterface;

/**
 * Class ProductUpdatedEventSubscriber.
 *
 * @package Drupal\alshaya_color_split\EventSubscriber
 */
class ProductUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * Color Split Manager.
   *
   * @var \Drupal\alshaya_color_split\AlshayaColorSplitManager
   */
  protected $colorSplitManager;

  /**
   * Cache Tags Invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_color_split\AlshayaColorSplitManager $color_split_manager
   *   Color Split Manager.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   Cache Tags Invalidator.
   */
  public function __construct(AlshayaColorSplitManager $color_split_manager,
                              CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->colorSplitManager = $color_split_manager;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::PRODUCT_PROCESSED_EVENT][] = ['onProductProcessed', 500];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductProcessed(ProductUpdatedEvent $event) {
    // Invalidate cache tags for all the products in same style.
    $entity = $event->getSku();
    $variants = $this->colorSplitManager->getProductsInStyle($entity, TRUE);
    foreach ($variants as $variant) {
      $this->cacheTagsInvalidator->invalidateTags($variant->getCacheTagsToInvalidate());
      $node = $variant->getPluginInstance()->getDisplayNode($variant, FALSE, FALSE);
      if ($node instanceof NodeInterface) {
        $this->cacheTagsInvalidator->invalidateTags($node->getCacheTagsToInvalidate());
      }
    }
  }

}
