<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\acq_sku\Entity\SKU;
use Psr\Log\LoggerInterface;
use Drupal\acq_commerce\SKUInterface;

/**
 * Class Product DyPage Type Event Subscriber.
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
   * Logger service.
   *
   * @var Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * ProductDyPageTypeEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager.
   * @param Psr\Log\LoggerInterface $logger
   *   The logger service.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    SkuManager $skuManager,
    LoggerInterface $logger
  ) {
    $this->routeMatch = $route_match;
    $this->skuManager = $skuManager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
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
        if (empty($productSku)) {
          $this->logger->notice('ProductDyPageTypeEventSubscriber: SKU not found for the Product ID: @nid.', ['@nid' => $node->id()]);
          return;
        }

        $productSku = SKU::loadFromSku($productSku);
        if (!($productSku instanceof SKUInterface)) {
          $this->logger->notice('ProductDyPageTypeEventSubscriber: SKU could not be loaded for the Product ID: @nid.', ['@nid' => $node->id()]);
          return;
        }
        if ($productSku->bundle() === 'configurable') {
          $combinations = $this->skuManager->getConfigurableCombinations($productSku);
          if (isset($combinations['by_sku'])) {
            $event->setDyContextData([strval(key($combinations['by_sku']))]);
          }
        }
        else {
          $event->setDyContextData([$productSku->getSku()]);
        }
      }
      elseif ($node->bundle() === 'rcs_product') {
        // We only have PDP `type`, don't have `data` for V3. It is handled in
        // 'alshaya_rcs_product_dy.js' file.
        $event->setDyContext('PRODUCT');
      }
    }
  }

}
