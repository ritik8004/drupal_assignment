<?php

namespace Drupal\alshaya_acm_product\Plugin\QueueWorker;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dynamic_yield\Service\ProductDeltaFeedApiWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_feed\AlshayaProductDeltaFeedHelper;
use Drupal\alshaya_acm_product\SkuManager;

/**
 * Processes product after any updates.
 *
 * @QueueWorker(
 *   id = "alshaya_post_process_product",
 *   title = @Translation("Alshaya Post Process Product"),
 * )
 */
class PostProcessProduct extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;

  /**
   * Queue Name.
   */
  const QUEUE_NAME = 'alshaya_post_process_product';

  /**
   * DY Product Delta Feed API Wrapper.
   *
   * @var \Drupal\dynamic_yield\Service\ProductDeltaFeedApiWrapper
   */
  protected $dyProductDeltaFeedApiWrapper;

  /**
   * Dynamic Yield config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $dyConfig;

  /**
   * Product Delta Feed Helper.
   *
   * @var Drupal\alshaya_feed\AlshayaProductDeltaFeedHelper
   */
  protected $productDeltaFeedHelper;

  /**
   * Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * PostProcessProduct constructor.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin unique id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\dynamic_yield\Service\ProductDeltaFeedApiWrapper $product_feed_api_wrapper
   *   DY Product Delta Feed API Wrapper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\alshaya_feed\AlshayaProductDeltaFeedHelper $product_delta_feed_helper
   *   Product Feed Helper.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ProductDeltaFeedApiWrapper $product_feed_api_wrapper,
                              ConfigFactoryInterface $config_factory,
                              AlshayaProductDeltaFeedHelper $product_delta_feed_helper,
                              SkuManager $skuManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dyProductDeltaFeedApiWrapper = $product_feed_api_wrapper;
    $this->dyConfig = $config_factory->get('dynamic_yield.settings');
    $this->productDeltaFeedHelper = $product_delta_feed_helper;
    $this->skuManager = $skuManager;
  }

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
    $feeds = $this->dyConfig->get('feeds');

    if (empty($feeds)) {
      $this->getLogger('PostProcessProduct')->notice('DY Feeds config is empty - dynamic_yield.settings:feeds.');
      return;
    }

    $entity = SKU::loadFromSku($sku);

    // Sanity check.
    $skuDelete = FALSE;
    $node = [];
    if (!($entity instanceof SKU)) {
      $this->getLogger('PostProcessProduct')->notice('SKU flagged for delete from dy product feed: @sku as not able to load SKU', [
        '@sku' => $sku,
      ]);

      $skuDelete = TRUE;
    }
    else {
      $node = $entity->getPluginInstance()->getDisplayNode($entity, FALSE);
    }

    if (!($node instanceof NodeInterface) || $skuDelete) {
      // Get children of the SKU.
      $children = $this->skuManager->getChildSkus($sku);

      // If children is empty then it's a simple SKU, set SKU oos in delta feed.
      if (empty($children)) {
        $this->markProductOutOfStock($feeds, $sku);
        return;
      }

      // Set SKU oos in delta feed.
      foreach ($children as $child_sku) {
        $this->markProductOutOfStock($feeds, $child_sku->getSku());
        return;
      }
    }

    $feed_data = $this->productDeltaFeedHelper->prepareProductFeedData($node->id());

    if (empty($feed_data)) {
      $this->markProductOutOfStock($feeds, $sku);

      $this->getLogger('PostProcessProduct')->warning('Feed data is empty for sku: @sku, node id: @nid.', [
        '@sku' => $sku,
        '@nid' => $node->id(),
      ]);
      return;
    }

    foreach ($feed_data as $sku => $data) {
      foreach ($feeds as $feed) {
        $this->dyProductDeltaFeedApiWrapper->productFeedUpsert($feed['api_key'], $feed['id'], $sku, ['data' => $data]);
      }

      $this->getLogger('PostProcessProduct')->notice('DY upsert API invoked. Processed product with sku: @sku.', [
        '@sku' => $sku,
      ]);
    }
  }

  /**
   * Delete SKU from feeds.
   *
   * @param array $feeds
   *   Feeds array.
   * @param string $sku
   *   SKU.
   */
  public function deleteFromFeed(array $feeds, string $sku) {
    foreach ($feeds as $feed) {
      $this->dyProductDeltaFeedApiWrapper->productFeedDelete($feed['api_key'], $feed['id'], $sku);
    }

    $this->getLogger('PostProcessProduct')->notice('DY delete API invoked. Processed product with sku: @sku.', [
      '@sku' => $sku,
    ]);
  }

  /**
   * Update Sku stock info on delta feed.
   *
   * @param array $feeds
   *   Feeds array.
   * @param string $sku
   *   SKU.
   */
  private function markProductOutOfStock(array $feeds, string $sku) {
    foreach ($feeds as $feed) {
      $data['data'] = $this->productDeltaFeedHelper->prepareFeedDataforSkuOos($sku);
      $this->dyProductDeltaFeedApiWrapper->productFeedPartialUpdate($feed['api_key'], $feed['id'], $sku, $data);
    }

    $this->getLogger('PostProcessProduct')->notice('DY partial update API invoked. Processed product with sku: @sku.', [
      '@sku' => $sku,
    ]);
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
      $container->get('dynamic_yield.product_feed_api_wrapper'),
      $container->get('config.factory'),
      $container->get('alshaya_feed.product_delta_feed'),
      $container->get('alshaya_acm_product.skumanager')
    );
  }

}
