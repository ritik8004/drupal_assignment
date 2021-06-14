<?php

namespace Drupal\alshaya_feed\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Queue\QueueFactory;

/**
 * Class Alshaya Manage Queue Command.
 *
 * @package Drupal\alshaya_feed\Commands
 */
class AlshayaManageQueue extends DrushCommands {
  /**
   * Queue factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * AlshayaManageQueue constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory service.
   */
  public function __construct(
    QueueFactory $queue_factory
  ) {
    $this->queueFactory = $queue_factory;
  }

  /**
   * Create product feed.
   *
   * @param array $options
   *   (optional) An array of options.
   *
   * @command alshaya_feed:manage-queue
   *
   * @aliases manage-queue
   *
   * @option name
   *   The name of the queue.
   * @option action
   *   The action to perform - read or delete.
   * @option skus
   *   The list of skus to delete.
   *
   * @usage drush manage-queue --name=alshaya_process_oos_product --action=read
   *   Displays the content of given queue - alshaya_process_oos_product.
   * @usage drush manage-queue --name=alshaya_process_oos_product --action=delete skus=123,234
   *   Deletes skus from alshaya_process_oos_product queue.
   */
  public function manageQueue(array $options = [
    'name' => '',
    'action' => 'read',
    'skus' => '',
    'dry-run' => FALSE,
  ]) {
    $name = $options['name'] ?? '';

    // Return if empty name.
    if (empty($name)) {
      $this->io()->writeln('Empty queue name.');
      return;
    }

    $action = $options['action'];
    $skus = $options['skus'] ? explode(',', $options['skus']) : '';

    // Return if action is delete and skus is empty.
    if ($action === 'delete' && empty($skus)) {
      $this->io()->writeln('SKU list empty for action delete.');
      return;
    }

    $dry_run = (bool) $options['dry-run'];
    $queue = $this->queueFactory->get($name);
    $items = [];

    // Claim all queue items.
    while ($item = $queue->claimItem()) {
      $items[] = $item;
    }

    // Based on action, display queue items or delete and
    // release the items at the end.
    foreach ($items as $item) {
      if ($action === 'read') {
        $this->io()->writeln($item->data);
        $queue->releaseItem($item);
        continue;
      }

      if ($action === 'delete' && in_array($item->data, $skus)) {
        $this->io()->writeln('Delete SKU ' . $item->data . ' from Queue.');

        if (!$dry_run) {
          $queue->deleteItem($item);
        }
        $queue->releaseItem($item);
      }
    }
  }

}
