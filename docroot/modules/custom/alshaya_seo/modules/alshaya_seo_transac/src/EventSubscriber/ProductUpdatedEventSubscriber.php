<?php

namespace Drupal\alshaya_seo_transac\EventSubscriber;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_seo_transac\AlshayaSitemapManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\NodeInterface;

/**
 * Class ProductUpdatedEventSubscriber.
 *
 * @package Drupal\alshaya_acm_product_category\EventSubscriber
 */
class ProductUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Product Category Manager.
   *
   * @var \Drupal\alshaya_seo_transac\AlshayaSitemapManager
   */
  protected $alshayaSitemapManager;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_seo_transac\AlshayaSitemapManager $alshaya_sitemap_manager
   *   Product Category Manager.
   */
  public function __construct(SkuManager $sku_manager,
                              AlshayaSitemapManager $alshaya_sitemap_manager) {
    $this->skuManager = $sku_manager;
    $this->alshayaSitemapManager = $alshaya_sitemap_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::PRODUCT_PROCESSED_EVENT][] = ['onProductProcessed', 600];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductProcessed(ProductUpdatedEvent $event) {
    $entity = $event->getSku();
    $node = $this->skuManager->getDisplayNode($entity);

    if ($node instanceof NodeInterface) {
      $this->alshayaSitemapManager->acqProductOperation($node->id(), 'node');
    }
  }

}
