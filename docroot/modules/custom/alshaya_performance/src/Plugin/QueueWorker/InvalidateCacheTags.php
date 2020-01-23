<?php

namespace Drupal\alshaya_performance\Plugin\QueueWorker;

use Drupal\alshaya_performance\Event\CacheTagInvalidatedEvent;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * InvalidateCacheTags.
 *
 * @QueueWorker(
 *   id = "alshaya_invalidate_cache_tags",
 *   title = @Translation("Alshaya Invalidate Cache Tags in Queue."),
 * )
 */
class InvalidateCacheTags extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;

  /**
   * Queue Name.
   */
  const QUEUE_NAME = 'alshaya_invalidate_cache_tags';

  /**
   * Cache Tags Invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * InvalidateCategoryListingCache constructor.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin unique id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   Cache Tags Invalidator.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              CacheTagsInvalidatorInterface $cache_tags_invalidator,
                              EventDispatcherInterface $dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->dispatcher = $dispatcher;
  }

  /**
   * Works on a single queue item.
   *
   * @param string $tag
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
  public function processItem($tag) {
    if (empty($tag)) {
      return;
    }

    // Invalid cache tags for node and sku.
    $this->cacheTagsInvalidator->invalidateTags([$tag]);

    $this->getLogger('InvalidateCacheTags')->notice('Invalidated cache tag @tag', [
      '@tag' => $tag,
    ]);

    $this->dispatcher->dispatch(
      CacheTagInvalidatedEvent::EVENT_NAME,
      (new CacheTagInvalidatedEvent($tag))
    );
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
      $container->get('cache_tags.invalidator'),
      $container->get('event_dispatcher')
    );
  }

}
