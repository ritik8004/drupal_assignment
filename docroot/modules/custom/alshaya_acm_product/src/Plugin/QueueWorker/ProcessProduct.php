<?php

namespace Drupal\alshaya_acm_product\Plugin\QueueWorker;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\alshaya_acm_product\Service\ProductProcessedManager;
use Drupal\alshaya_acm_product\Service\ProductQueueUtility;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\RequeueException;
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
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Product Queue Utility.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductQueueUtility
   */
  protected $queueUtility;

  /**
   * Static cache for all the products processed in current process.
   *
   * @var array
   */
  protected static $processedProducts = [];

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $sku = $data;
    $nid = 0;

    if (is_array($data)) {
      $sku = $data['sku'];
      $nid = (int) $data['nid'];
    }

    // Delete the item right away so we get new entry if updated in parallel.
    $this->queueUtility->deleteItem($data);

    // If the product is already processed once in the current drush request
    // requeue and do not process again in same request.
    if (isset(self::$processedProducts[$sku], self::$processedProducts[$sku][$nid])) {
      // Kill the process if we have already re-queued the product once.
      if (self::$processedProducts[$sku][$nid] > 1) {
        $this->getLogger('ProcessProduct')->notice('Killing the process as product re-queued twice already in current process. Sku: @sku, nid: @nid.', [
          '@sku' => $sku,
          '@nid' => $nid,
        ]);

        exit(0);
      }

      $this->getLogger('ProcessProduct')->notice('Re-queuing product as it is already processed in current process. Sku: @sku, nid: @nid.', [
        '@sku' => $sku,
        '@nid' => $nid,
      ]);

      // Increase the counter so we can kill the process after re-queuing twice.
      self::$processedProducts[$sku][$nid]++;

      $this->queueUtility->queueProduct($sku, $nid);
      return;
    }

    // Update static cache to record - product is processed already once.
    self::$processedProducts[$sku][$nid] = 1;

    try {
      // Reset all static caches before processing a product.
      $this->entityTypeManager->getStorage('node')->resetCache();
      drupal_static_reset('loadFromSku');

      $this->processSku($sku, $nid);
    }
    catch (RequeueException $e) {
      // @todo post Drupal 9 upgrade to handle Delayed Requeue Exception too.
      // @see https://www.drupal.org/project/drupal/issues/3116478
      // Here we already achieve that in a way by re-queuing the product in the
      // end instead of releasing the lock.
      $this->queueUtility->queueProduct($sku, $nid);
    }
    catch (\Exception $e) {
      $this->getLogger('ProcessProduct')->error('Exception occurred while processing the product with sku: @sku, nid: @nid, exception: @message', [
        '@sku' => $sku,
        '@nid' => $nid,
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Process the product.
   *
   * @param string $sku
   *   SKU to process.
   * @param int $nid
   *   Node ID of the SKU.
   */
  protected function processSku(string $sku, int $nid) {
    $entity = SKU::loadFromSku($sku);

    // Sanity check.
    if (!($entity instanceof SKU)) {
      $this->getLogger('ProcessProduct')->notice('Skipping process of product with sku: @sku as not able to load SKU', [
        '@sku' => $sku,
      ]);

      $this->deleteFromIndexes($nid);
      return;
    }

    // We expect only those SKUs here which are visible in frontend.
    // So either configurable SKU or simple one visible in frontend.
    $node = $entity->getPluginInstance()->getDisplayNode($entity, FALSE);
    if (!($node instanceof NodeInterface)) {
      $this->deleteFromIndexes($nid);

      if (!($this->skuManager->isSkuFreeGift($entity))) {
        $this->getLogger('ProcessProduct')->notice('Skipping process of product with sku: @sku as Node not available', [
          '@sku' => $entity->getSku(),
        ]);
        return;
      }
    }

    // Disable re-queueing while processing.
    self::$processingItem = TRUE;

    // Do all invalidations in one go.
    // Invalid cache tags for sku.
    $cache_tags = $entity->getCacheTagsToInvalidate();

    if ($node) {
      // Invalid cache tags for node if available.
      $cache_tags = Cache::mergeTags($cache_tags, $node->getCacheTagsToInvalidate());
    }

    // Invalidate our custom cache tags.
    $cache_tags = Cache::mergeTags(
      $cache_tags,
      ProductCacheManager::getAlshayaProductTags($entity)
    );

    $this->cacheTagsInvalidator->invalidateTags($cache_tags);

    $variants = $entity->bundle() === 'configurable'
      ? Configurable::getChildSkus($entity)
      : [];

    $translation_languages = $node
      ? $node->getTranslationLanguages()
      : $entity->getTranslationLanguages();

    foreach ($translation_languages as $language) {
      $translation = SKU::loadFromSku($entity->getSku(), $language->getId());

      foreach ($variants as $variant) {
        $variant_sku = SKU::loadFromSku($variant, $language->getId(), FALSE);
        if ($variant_sku instanceof SKU) {
          // Download product images for all the variants of the product.
          $this->imagesManager->getProductMedia($variant_sku, 'pdp', TRUE);
          $this->imagesManager->getProductMedia($variant_sku, 'pdp', FALSE);
          if ($node) {
            // Mark the variant as processed now.
            $this->productProcessedManager->markProductProcessed($variant_sku->getSku());
          }
        }
      }

      // Download product images for product and warm up caches.
      $this->imagesManager->getProductMedia($translation, 'pdp', TRUE);
      $this->imagesManager->getProductMedia($translation, 'pdp', FALSE);
      if ($node) {
        // Mark the product as processed now.
        $this->productProcessedManager->markProductProcessed($translation->getSku());
        // Trigger event for other modules to take action.
        // For instance alshaya_search_api to index items.
        $event = new ProductUpdatedEvent($translation, ProductUpdatedEvent::PRODUCT_PROCESSED);
        $this->dispatcher->dispatch(ProductUpdatedEvent::PRODUCT_PROCESSED_EVENT, $event);
      }
    }

    $this->getLogger('ProcessProduct')->notice('Processed product with sku: @sku', [
      '@sku' => $entity->getSku(),
    ]);
  }

  /**
   * Wrapper function to get active indexes.
   *
   * @return \Drupal\search_api\Entity\Index[]
   *   Enabled and writable indexes array.
   */
  protected function getIndexes() {
    static $indexes;
    if (!empty($indexes)) {
      return $indexes;
    }

    $indexes = $this->entityTypeManager->getStorage('search_api_index')->loadMultiple();

    /** @var \Drupal\search_api\Entity\Index $index */
    foreach ($indexes as $id => $index) {
      if ($index->isReadOnly() || !($index->status())) {
        unset($indexes[$id]);
      }
    }

    return $indexes;
  }

  /**
   * Wrapper function to delete nodes from Indexes.
   */
  protected function deleteFromIndexes(int $nid) {
    if (empty($nid)) {
      return;
    }

    $indexes = $this->getIndexes();
    if (!$indexes) {
      return;
    }

    // Remove the search items for all the entity's translations.
    foreach ($indexes as $index) {
      $index->trackItemsDeleted('entity:node', [$nid]);

      $this->getLogger('ProcessProduct')->notice('Deleted product from index: @index, nid: @nid.', [
        '@nid' => $nid,
        '@index' => $index->id(),
      ]);
    }
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Type Manager.
   * @param \Drupal\alshaya_acm_product\Service\ProductQueueUtility $queue_utility
   *   Product Queue Utility.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_images_manager,
                              EventDispatcherInterface $dispatcher,
                              CacheTagsInvalidatorInterface $cache_tags_invalidator,
                              ProductProcessedManager $product_processed_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ProductQueueUtility $queue_utility) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->skuManager = $sku_manager;
    $this->imagesManager = $sku_images_manager;
    $this->dispatcher = $dispatcher;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->productProcessedManager = $product_processed_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->queueUtility = $queue_utility;
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
      $container->get('alshaya_acm_product.product_processed_manager'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_acm_product.product_queue_utility')
    );
  }

}
