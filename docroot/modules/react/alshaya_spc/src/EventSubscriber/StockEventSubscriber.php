<?php

namespace Drupal\alshaya_spc\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Manages stock mismatch scenarios.
 *
 * We have App\EventListener\StockEventListener that also works on stock
 * updates. But activities like clearing cachetags of SKUs cannot be done there
 * since that is the middleware application. So we do such activities here.
 */
class StockEventSubscriber implements EventSubscriberInterface {

  /**
   * Contains the SKU code of skus for which stock has been refreshed.
   *
   * @var array
   */
  protected static $skusWithRefreshedStock = [];

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The class constructor.
   *
   * @param Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->logger = $logger_factory->get('alshaya_spc');
  }

  /**
   * Sets the SKUs for which stock has been refreshed.
   *
   * @param string $sku
   *   The SKU code.
   */
  public static function setSkusWithRefreshedStock($sku) {
    self::$skusWithRefreshedStock[] = $sku;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::TERMINATE => [
        ['onKernelTerminate'],
      ],
    ];
  }

  /**
   * Invoked by the TERMINATE kernel event.
   *
   * Here we clear the cache tags of the skus for which stock has been
   * refreshed.
   *
   * @param \Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   The event object.
   */
  public function onKernelTerminate(PostResponseEvent $event) {
    if (empty(self::$skusWithRefreshedStock)) {
      return;
    }

    $cache_tags = [];

    foreach (self::$skusWithRefreshedStock as $sku) {
      $sku_entity = SKU::loadFromSku($sku);
      if ($sku_entity instanceof SKUInterface) {
        $cache_tags = Cache::mergeTags($cache_tags, $sku_entity->getCacheTags());
      }
    }

    Cache::invalidateTags($cache_tags);
    $this->logger->info('Cleared cache tags for skus with refreshed stock: @skus', [
      '@skus' => implode(',', self::$skusWithRefreshedStock),
    ]);
  }

}
