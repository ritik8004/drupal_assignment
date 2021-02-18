<?php

namespace Drupal\alshaya_spc\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Plugin\rest\resource\StockResource;
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
   * Contains the SKU code of the skus for which cache has to be invalidated.
   *
   * @var array
   */
  protected static $skusForCacheInvalidation = [];

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
   */
  public function __construct(
    LoggerInterface $logger
  ) {
    $this->logger = $logger;
  }

  /**
   * Set the optional purger service.
   *
   * @param \Drupal\purge\Plugin\Purge\Purger\PurgersServiceInterface $purgers
   *   The purgers service.
   */
  public function setPurger(PurgersServiceInterface $purgers) {
    $this->purgers = $purgers;
  }

  /**
   * Set the optional purge processor service.
   *
   * @param \Drupal\purge\Plugin\Purge\Processor\ProcessorsServiceInterface $processors
   *   The purge processors service.
   */
  public function setPurgeProcessor(ProcessorsServiceInterface $processors) {
    $this->purgeProcessor = $processors->get('lateruntime');
  }

  /**
   * Set the optional purge invalidation factory service.
   *
   * @param \Drupal\purge\Plugin\Purge\Invalidation\InvalidationsServiceInterface $purge_invalidations_factory
   *   The purge invalidations factory service.
   */
  public function setPurgeInvalidationFactory(InvalidationsServiceInterface $purge_invalidations_factory) {
    $this->purgeInvalidationsFactory = $purge_invalidations_factory;
  }

  /**
   * Sets the SKUs for which stock has been refreshed.
   *
   * @param string $sku
   *   The SKU code.
   */
  public static function setSkusForCacheInvalidation(string $sku) {
    self::$skusForCacheInvalidation[] = $sku;
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
    if (!isset($this->purgers) || (isset($this->purgers) && empty(self::$skusForCacheInvalidation))) {
      return;
    }

    // We want the SKU cache tags to get cleared only when the SKU is processed
    // in the alshaya_process_product queue. And that will happen when Magento
    // pushes the SKU with stock update to Drupal.
    // Hence we invalidate only the Cache tags for the Stock API here to mark
    // real time stock update has happened.
    $purge_tags = [];
    $cache_tags_to_invalidate = [];

    foreach (self::$skusForCacheInvalidation as $sku) {
      $sku_entity = SKU::loadFromSku($sku);
      if ($sku_entity instanceof SKUInterface) {
        $cache_tags_to_invalidate = Cache::mergeTags($cache_tags_to_invalidate, [StockResource::CACHE_PREFIX . $sku_entity->id()]);
      }
    }

    Cache::invalidateTags($cache_tags_to_invalidate);

    // Now prepare data to purge the varnish cache.
    foreach ($cache_tags_to_invalidate as $cache_tag) {
      $purge_tags[] = $this->purgeInvalidationsFactory->get('tag', $cache_tag);
    }

    try {
      // This will immediately invalidate the cache tags.
      $this->purgers->invalidate($this->purgeProcessor, $purge_tags);
      $this->logger->info('Invalidated cache tags on stock refresh for the following skus: @skus', [
        '@skus' => implode(',', self::$skusForCacheInvalidation),
      ]);
    }
    catch (\Exception $e) {
      $this->logger->notice('Exception occurred while invalidating cache tags on stock refresh: @exception, for skus @skus', [
        '@exception' => $e->getMessage(),
        '@skus' => implode(',', self::$skusForCacheInvalidation),
      ]);
    }
  }

}
