<?php

namespace Drupal\alshaya_spc\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;
use Drupal\purge\Plugin\Purge\Invalidation\InvalidationsServiceInterface;
use Drupal\purge\Plugin\Purge\Processor\ProcessorsServiceInterface;
use Drupal\purge\Plugin\Purge\Purger\PurgersServiceInterface;
use Psr\Log\LoggerInterface;
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
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The purgers service.
   *
   * @var \Drupal\purge\Plugin\Purge\Purger\PurgersServiceInterface
   */
  protected $purgers;

  /**
   * The purge processors service.
   *
   * @var \Drupal\purge\Plugin\Purge\Processor\ProcessorInterface
   */
  protected $purgeProcessor;

  /**
   * The purge invalidations factory service.
   *
   * @var \Drupal\purge\Plugin\Purge\Invalidation\InvalidationsServiceInterface
   */
  protected $purgeInvalidationsFactory;

  /**
   * The class constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger factory service.
   * @param \Drupal\purge\Plugin\Purge\Purger\PurgersServiceInterface $purgers
   *   The purgers service.
   * @param \Drupal\purge\Plugin\Purge\Processor\ProcessorsServiceInterface $processors
   *   The purge processors service.
   * @param \Drupal\purge\Plugin\Purge\Invalidation\InvalidationsServiceInterface $purge_invalidations_factory
   *   The purge invalidations factory service.
   */
  public function __construct(
    LoggerInterface $logger,
    PurgersServiceInterface $purgers,
    ProcessorsServiceInterface $processors,
    InvalidationsServiceInterface $purge_invalidations_factory
  ) {
    $this->logger = $logger;
    $this->purgers = $purgers;
    $this->purgeProcessor = $processors->get('lateruntime');
    $this->purgeInvalidationsFactory = $purge_invalidations_factory;
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
    $purge_tags = [];

    foreach (self::$skusWithRefreshedStock as $sku) {
      $sku_entity = SKU::loadFromSku($sku);
      if ($sku_entity instanceof SKUInterface) {
        $cache_tags = Cache::mergeTags($cache_tags, $sku_entity->getCacheTags());
      }
    }

    if (empty($cache_tags)) {
      return;
    }

    Cache::invalidateTags($cache_tags);

    // Now prepare data to purge the varnish cache.
    foreach ($cache_tags as $cache_tag) {
      $purge_tags[] = $this->purgeInvalidationsFactory->get('tag', $cache_tag);
    }

    try {
      $this->purgers->invalidate($this->purgeProcessor, $purge_tags);
      $this->logger->info('Invalidated cache tags on stock refresh for the following skus: @skus', [
        '@skus' => implode(',', self::$skusWithRefreshedStock),
      ]);
    }
    catch (\Exception $e) {
      $this->logger->notice('Exception occurred while invalidating cache tags on stock refresh: @exception, for skus @skus', [
        '@exception' => $e->getMessage(),
        '@skus' => implode(',', self::$skusWithRefreshedStock),
      ]);
    }
  }

}
