<?php

namespace Drupal\alshaya_seo_transac\EventSubscriber;

use Drupal\alshaya_search_algolia\Event\AlshayaAlgoliaProductIndexEvent;
use Drupal\alshaya_seo_transac\AlshayaGtmManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlgoliaIndexEventSubscriber.
 *
 * @package Drupal\alshaya_seo_transac\EventSubscriber
 */
class AlgoliaIndexEventSubscriber implements EventSubscriberInterface {

  /**
   * GTM Manager.
   *
   * @var \Drupal\alshaya_seo_transac\AlshayaGtmManager
   */
  protected $gtmManager;

  /**
   * AlgoliaIndexEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_seo_transac\AlshayaGtmManager $gtm_manager
   *   GTM Manager.
   */
  public function __construct(AlshayaGtmManager $gtm_manager) {
    $this->gtmManager = $gtm_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[AlshayaAlgoliaProductIndexEvent::PRODUCT_INDEX][] = ['onProductIndex', 100];
    return $events;
  }

  /**
   * Index GTM data.
   *
   * @param \Drupal\alshaya_search_algolia\Event\AlshayaAlgoliaProductIndexEvent $event
   *   The event object.
   */
  public function onProductIndex(AlshayaAlgoliaProductIndexEvent $event) {
    $gtmContainer = [];
    $gtmContainer['route_name'] = 'view.search.page';
    $gtmContainer['route_params'] = [];
    $gtmContainer['pathinfo'] = NULL;
    $gtmContainer['query'] = [];

    $this->gtmManager->setGtmContainer($gtmContainer);

    $item = $event->getItem();

    $item['productType'] = $event->getSkuEntity()->bundle();
    $item['gtm'] = $this->gtmManager->fetchProductGtmAttributes($event->getNodeEntity(), 'search_result');

    $event->setItem($item);
  }

}
