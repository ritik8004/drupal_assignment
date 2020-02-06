<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductDyPageTypeEventSubscriber.
 *
 * @package Drupal\alshaya_acm_product\EventSubscriber
 */
class ProductDyPageTypeEventSubscriber implements EventSubscriberInterface {

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Sku Manager service.
   *
   * @var Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * ProductDyPageTypeEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    SkuManager $skuManager
  ) {
    $this->routeMatch = $route_match;
    $this->skuManager = $skuManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['dy.set.context'][] = ['setContextProduct', 250];
    return $events;
  }

  /**
   * Set PRODUCT Context for Dynamic yield script.
   *
   * @param \Drupal\dynamic_yield\Event $event
   *   Dispatched Event.
   */
  public function setContextProduct(Event $event) {
    if ($this->routeMatch->getRouteName() !== 'entity.node.canonical') {
      return;
    }
    if (($node = $this->routeMatch->getParameter('node')) && $node instanceof NodeInterface) {
      if ($node->bundle() == 'acq_product') {
        $event->setDyContext('PRODUCT');
        $productSku = $this->skuManager->getSkuForNode($node);
        $event->setDyContextData([$productSku]);
      }
    }
  }

}
