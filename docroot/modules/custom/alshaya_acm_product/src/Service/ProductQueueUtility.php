<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\alshaya_acm_product\Plugin\QueueWorker\ProcessProduct;
use Drupal\Core\Database\Connection;
use Drupal\Core\Queue\QueueFactory;

/**
 * Class ProductQueueUtility.
 */
class ProductQueueUtility {

  /**
   * Queue factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * ProductQueueUtility constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   */
  public function __construct(QueueFactory $queue_factory,
                              Connection $connection) {
    $this->queueFactory = $queue_factory;
    $this->connection = $connection;
  }

  /**
   * Queue product for background processing.
   *
   * @param string $sku
   *   Product SKU.
   */
  public function queueProduct(string $sku) {
    $this->getQueue()->createItem($sku);
  }

  /**
   * Process all the products again.
   *
   * Usually done in an update hook when we want to reindex or change caches.
   */
  public function queueAllProducts() {
    $query = $this->connection->select('node__field_skus', 'nfs');
    $query->join('node_field_data', 'nfd', 'nfd.nid = nfs.entity_id AND nfd.langcode = nfs.langcode');
    $query->condition('nfd.default_langcode', 1);
    $query->condition('nfd.type', 'acq_product');
    $query->isNotNull('nfs.field_skus_value');
    $query->addField('nfs', 'field_skus_value');

    $products = $query->execute()->fetchAllKeyed(0, 0);

    foreach ($products as $sku) {
      $this->queueProduct($sku);
    }
  }

  /**
   * Static wrapper to get queue.
   *
   * @return \Drupal\Core\Queue\QueueInterface
   *   Queue.
   */
  protected function getQueue() {
    static $queue;

    if (empty($queue)) {
      $queue = $this->queueFactory->get(ProcessProduct::QUEUE_NAME);
    }

    return $queue;
  }

}
