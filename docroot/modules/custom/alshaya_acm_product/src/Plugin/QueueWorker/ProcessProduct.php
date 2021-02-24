<?php

namespace Drupal\alshaya_acm_product\Plugin\QueueWorker;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\alshaya_acm_product\Service\ProductProcessedManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Processes product after any updates.
 *
 * @QueueWorker(
 *   id = "alshaya_process_product",
 *   title = @Translation("Alshaya Process Product"),
 * )
 */
class ProcessProduct extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;

  /**
   * Queue Name.
   */
  const QUEUE_NAME = 'alshaya_process_product';

  /**
   * Flag to indicate if processing item currently.
   *
   * @var bool
   */
  public static $processingItem = FALSE;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SKU Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $imagesManager;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Cache Tags Invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Product Processed Manager.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductProcessedManager
   */
  protected $productProcessedManager;

  /**
   * Works on a single queue item.
   *
   * @param mixed $sku
   *   The data that was passed to
   *   \Drupal\Core\Queue\QueueInterface::createItem() when the item was queued.
   *
   * @throws \Drupal\Core\Queue\RequeueException
   *   Processing is not yet finished. This will allow another process to claim
   *   the item immediately.
   * @throws \Exception
   *   A QueueWorker plugin may throw an exception to indicate there was a
   *   problem. The cron process will log the exception, and leave the item in
   *   the queue to be processed again later.
   * @throws \Drupal\Core\Queue\SuspendQueueException
   *   More specifically, a SuspendQueueException should be thrown when a
   *   QueueWorker plugin is aware that the problem will affect all subsequent
   *   workers of its queue. For example, a callback that makes HTTP requests
   *   may find that the remote server is not responding. The cron process will
   *   behave as with a normal Exception, and in addition will not attempt to
   *   process further items from the current item's queue during the current
   *   cron run.
   *
   * @see \Drupal\Core\Cron::processQueues()
   */
  public function processItem($sku) {
    $entity = SKU::loadFromSku($sku);

    // Sanity check.
    if (!($entity instanceof SKU)) {
      $this->getLogger('ProcessProduct')->notice('Skipping process of product with sku: @sku as not able to load SKU', [
        '@sku' => $sku,
      ]);

      return;
    }

    // We expect only those SKUs here which are visible in frontend.
    // So either configurable SKU or simple one visible in frontend.
    $node = $entity->getPluginInstance()->getDisplayNode($entity, FALSE);
    if (!($node instanceof NodeInterface)) {
      if ($this->skuManager->isSkuFreeGift($entity)) {
        $this->getProessProductMedia($entity);
        return;
      }
      $this->getLogger('ProcessProduct')->notice('Skipping process of product with sku: @sku as Node not available', [
        '@sku' => $entity->getSku(),
      ]);
      return;
    }
    $this->getProessProductMedia($entity, $node);
  }

  /**
   * AcqPromotionAttachQueue constructor.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin unique id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   Cache Tags Invalidator.
   * @param \Drupal\alshaya_acm_product\Service\ProductProcessedManager $product_processed_manager
   *   Product Processed Manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_images_manager,
                              EventDispatcherInterface $dispatcher,
                              CacheTagsInvalidatorInterface $cache_tags_invalidator,
                              ProductProcessedManager $product_processed_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->skuManager = $sku_manager;
    $this->imagesManager = $sku_images_manager;
    $this->dispatcher = $dispatcher;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->productProcessedManager = $product_processed_manager;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('event_dispatcher'),
      $container->get('cache_tags.invalidator'),
      $container->get('alshaya_acm_product.product_processed_manager')
    );
  }

  /**
   * Function to get media for Free Gift SKU.
   */
  private function getProessProductMedia(SKU $entity, NodeInterface $node = NULL) {
    // Disable re-queueing while processing.
    self::$processingItem = TRUE;

    // Invalid cache tags for node.
    if ($node) {
      $this->cacheTagsInvalidator->invalidateTags($node->getCacheTagsToInvalidate());
    }
    // Invalid cache tags for sku.
    $this->cacheTagsInvalidator->invalidateTags($entity->getCacheTagsToInvalidate());

    // Invalidate our custom cache tags.
    $sku_tags = ProductCacheManager::getAlshayaProductTags($entity);
    $this->cacheTagsInvalidator->invalidateTags($sku_tags);

    if ($node) {
      $variants = $entity->bundle() === 'configurable'
      ? Configurable::getChildSkus($entity)
      : [];

      foreach ($node->getTranslationLanguages() as $language) {
        $translation = SKU::loadFromSku($entity->getSku(), $language->getId());

        foreach ($variants as $variant) {
          $variant_sku = SKU::loadFromSku($variant, $language->getId(), FALSE);
          if ($variant_sku instanceof SKU) {
            // Download product images for all the variants of the product.
            $this->imagesManager->getProductMedia($variant_sku, 'pdp', TRUE);
            $this->imagesManager->getProductMedia($variant_sku, 'pdp', FALSE);

            // Mark the variant as processed now.
            $this->productProcessedManager->markProductProcessed($variant_sku->getSku());
          }
        }

        // Download product images for product and warm up caches.
        $this->imagesManager->getProductMedia($translation, 'pdp', TRUE);
        $this->imagesManager->getProductMedia($translation, 'pdp', FALSE);
        // Mark the product as processed now.
        $this->productProcessedManager->markProductProcessed($translation->getSku());

        // Trigger event for other modules to take action.
        // For instance alshaya_search_api to index items.
        $event = new ProductUpdatedEvent($translation, ProductUpdatedEvent::PRODUCT_PROCESSED);
        $this->dispatcher->dispatch(ProductUpdatedEvent::PRODUCT_PROCESSED_EVENT, $event);
      }
    }
    else {
      foreach ($entity->getTranslationLanguages() as $language) {
        $translation = SKU::loadFromSku($entity->getSku(), $language->getId());
        // Download product images for product and warm up caches.
        $this->imagesManager->getProductMedia($translation, 'pdp', TRUE);
        $this->imagesManager->getProductMedia($translation, 'pdp', FALSE);
      }
    }
    $this->getLogger('ProcessProduct')->notice('Processed product with sku: @sku', [
      '@sku' => $entity->getSku(),
    ]);
  }

}
