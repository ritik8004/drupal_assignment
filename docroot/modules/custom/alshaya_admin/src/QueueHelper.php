<?php

namespace Drupal\alshaya_admin;

use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Queue\RequeueException;
use Drupal\Core\Queue\SuspendQueueException;

/**
 * Class Queue Helper.
 */
class QueueHelper {

  /**
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The queue plugin manager.
   *
   * @var \Drupal\Core\Queue\QueueWorkerManagerInterface
   */
  protected $queueManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue service.
   * @param \Drupal\Core\Queue\QueueWorkerManagerInterface $queue_manager
   *   The queue plugin manager.
   */
  public function __construct(QueueFactory $queue_factory, QueueWorkerManagerInterface $queue_manager) {
    $this->queueFactory = $queue_factory;
    $this->queueManager = $queue_manager;
  }

  /**
   * Process the given queues.
   *
   * @param array $queue_names
   *   An array of queues to process.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *
   * @see \Drupal\Cron\Cron::processQueues()
   */
  public function processQueues(array $queue_names = []) {
    foreach ($queue_names as $queue_name) {
      $queues = $this->queueManager->getDefinitions();

      // In case the given queues does not exist.
      if (!isset($queues[$queue_name])) {
        return;
      }

      $queue_worker = $this->queueManager->createInstance($queue_name);
      $queue = $this->queueFactory->get($queue_name);

      while ($item = $queue->claimItem()) {
        try {
          $queue_worker->processItem($item->data);
          $queue->deleteItem($item);
        }
        catch (RequeueException $e) {
          // The worker requested the task be immediately requeued.
          $queue->releaseItem($item);
        }
        catch (SuspendQueueException $e) {
          // If the worker indicates there is a problem with the whole queue,
          // release the item and skip to the next queue.
          $queue->releaseItem($item);

          watchdog_exception('cron', $e);

          // Skip to the next queue.
          continue;
        }
        catch (\Exception $e) {
          // In case of any other kind of exception, log it and leave the item
          // in the queue to be processed again later.
          watchdog_exception('cron', $e);
        }
      }
    }
  }

}
