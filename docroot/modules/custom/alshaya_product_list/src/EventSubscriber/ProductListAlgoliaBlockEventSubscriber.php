<?php

namespace Drupal\alshaya_product_list\EventSubscriber;

use Drupal\alshaya_algolia_react\Event\ToggleAlgoliaProductListEvent;
use Drupal\alshaya_product_list\Service\AlshayaProductListHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductListAlgoliaBlockEventSubscriber.
 *
 * Toggle algolia product list block.
 *
 * @package Drupal\alshaya_product_list\EventSubscriber
 */
class ProductListAlgoliaBlockEventSubscriber implements EventSubscriberInterface {

  /**
   * Alshaya product list helper.
   *
   * @var \Drupal\alshaya_product_list\Service\AlshayaProductListHelper
   */
  protected $alshayaProductListHelper;

  /**
   * ProductListAlgoliaBlockEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_product_list\Service\AlshayaProductListHelper $alshaya_product_list_helper
   *   Product Processed Manager.
   */
  public function __construct(AlshayaProductListHelper $alshaya_product_list_helper) {
    $this->alshayaProductListHelper = $alshaya_product_list_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ToggleAlgoliaProductListEvent::EVENT_NAME][] = ['toggleProductListAlgoliaBlock'];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_algolia_react\Event\ToggleAlgoliaProductListEvent $event
   *   Event object.
   */
  public function toggleProductListAlgoliaBlock(ToggleAlgoliaProductListEvent $event) {
    $this->alshayaProductListHelper->toggleAlgoliaProductList($event->getOperation());
  }

}
