<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\Core\Database\Connection;

/**
 * Class Product Processed Manager.
 *
 * @package Drupal\alshaya_acm_product\Service
 */
class ProductProcessedManager {

  public const TABLE_NAME = 'product_processed';

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Static cache to reduce queries.
   *
   * @var array
   */
  protected static $processed = [];

  /**
   * ProductProcessedManager constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Check if product is processed or not.
   *
   * @param string $sku
   *   SKU to check.
   *
   * @return bool
   *   TRUE if processed already.
   */
  public function isProductProcessed(string $sku) {
    if (isset(self::$processed[$sku])) {
      return self::$processed[$sku];
    }

    $query = $this->connection->select(self::TABLE_NAME);
    $query->addField(self::TABLE_NAME, 'sku');
    $query->condition('sku', $sku);
    $result = $query->execute()->fetchCol();
    self::$processed[$sku] = ((is_countable($result) ? count($result) : 0) > 0);
    return self::$processed[$sku];
  }

  /**
   * Mark product is processed.
   *
   * @param string $sku
   *   SKU to mark.
   */
  public function markProductProcessed(string $sku) {
    if ($this->isProductProcessed($sku)) {
      return;
    }

    $this->connection
      ->insert(self::TABLE_NAME)
      ->fields(['sku' => $sku])
      ->execute();

    unset(self::$processed[$sku]);
  }

  /**
   * Remove product from processed list.
   *
   * @param string $sku
   *   SKU to remove.
   */
  public function removeProduct(string $sku) {
    $this->connection
      ->delete(self::TABLE_NAME)
      ->condition('sku', $sku)
      ->execute();

    unset(self::$processed[$sku]);
  }

}
