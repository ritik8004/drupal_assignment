<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\alshaya_acm_product\Plugin\QueueWorker\ProcessProduct;
use Drupal\Core\Database\Connection;
use Drupal\Core\Queue\QueueFactory;

/**
 * Class Product Queue Utility.
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
   * @param string|int|null $nid
   *   Node ID for the product.
   */
  public function queueProduct(string $sku, $nid = 0) {
    // Always convert node id to integer to have it consistent every-time.
    $nid = (int) $nid;

    $this->getQueue()->createItem([
      'sku' => $sku,
      'nid' => $nid,
    ]);
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
    $query->addField('nfd', 'nid');
    $query->addField('nfs', 'field_skus_value');

    $products = $query->execute()->fetchAllKeyed(0, 1);

    foreach ($products as $nid => $sku) {
      $this->queueProduct($sku, $nid);
    }
  }

  /**
   * Queue all products for the provided SKUs.
   *
   * Takes care of finding parent SKUs if child SKUs given.
   *
   * @param array $skus
   *   SKUs to queue.
   */
  public function queueAvailableProductsForSkus(array $skus) {
    // First get all the parent skus if available.
    $query = $this->connection->select('acq_sku_field_data', 'acq_sku');
    $query->addField('acq_sku', 'sku');
    $query->join('acq_sku__field_configured_skus', 'child_sku', 'acq_sku.id = child_sku.entity_id');
    $query->condition('child_sku.field_configured_skus_value', $skus, 'IN');
    $parents = $query->execute()->fetchCol();

    // Merge parents and skus provided.
    // There may be some simple products which are available as both
    // configurable and simple.
    $skus = array_merge($parents, $skus);

    // Filter out and queue only those for which we have node available.
    $query = $this->connection->select('node__field_skus', 'nfs');
    $query->join('node_field_data', 'nfd', 'nfd.nid = nfs.entity_id AND nfd.langcode = nfs.langcode');
    $query->condition('nfd.default_langcode', 1);
    $query->condition('nfd.type', 'acq_product');
    $query->condition('nfs.field_skus_value', $skus, 'IN');
    $query->addField('nfd', 'nid');
    $query->addField('nfs', 'field_skus_value');

    $products = $query->execute()->fetchAllKeyed(0, 1);

    foreach ($products as $nid => $sku) {
      $this->queueProduct($sku, $nid);
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
