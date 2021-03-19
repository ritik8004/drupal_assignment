<?php

namespace Drupal\alshaya_feed\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\Plugin\QueueWorker\PostProcessProduct;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Product Processed Event Subscriber.
 *
 * @package Drupal\alshaya_acm_product_category\EventSubscriber
 */
class ProductProcessedEventSubscriber implements EventSubscriberInterface {

  /**
   * Queue factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Dynamic Yield config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $dyConfig;

  /**
   * ProductProcessedEventSubscriber constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(QueueFactory $queue_factory,
                              ConfigFactoryInterface $config_factory) {
    $this->queueFactory = $queue_factory;
    $this->dyConfig = $config_factory->get('dynamic_yield.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::PRODUCT_PROCESSED_EVENT][] = [
      'onProductProcessed',
      900,
    ];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductProcessed(ProductUpdatedEvent $event) {
    if (!empty($this->dyConfig->get('feeds'))) {
      $this->queueFactory->get(PostProcessProduct::QUEUE_NAME)->createItem($event->getSku());
    }
  }

}
