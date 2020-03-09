<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * Class PaymentData.
 *
 * @package App\Service
 */
class PaymentData {

  const TABLE_NAME = 'middleware_payment_data';

  /**
   * The database connection.
   *
   * @var \Doctrine\DBAL\Connection
   */
  protected $connection;

  /**
   * PaymentData constructor.
   *
   * @param \Doctrine\DBAL\Connection $connection
   *   Database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Get payment data for the cart id.
   *
   * @param int $cart_id
   *   Cart ID.
   *
   * @return array
   *   Payment Data.
   *
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getPaymentDataByCartId(int $cart_id) {
    $query = sprintf('SELECT * FROM %s WHERE cart_id = ? ORDER BY timestamp DESC limit 0, 1', self::TABLE_NAME);
    $result = $this->connection->executeQuery($query, [$cart_id], [ParameterType::INTEGER]);

    $row = $result->fetch();
    return $row ? unserialize($row->data) : NULL;
  }

  /**
   * Get payment data for the unique payment token id.
   *
   * @param string $unique_id
   *   Unique ID.
   *
   * @return array
   *   Payment Data.
   *
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getPaymentDataByUniqueId(string $unique_id) {
    $query = sprintf('SELECT * FROM %s WHERE unique_id = ? ORDER BY timestamp DESC limit 0, 1', self::TABLE_NAME);
    $result = $this->connection->executeQuery($query, [$unique_id], [ParameterType::STRING]);

    $row = (array) $result->fetch();
    if (!empty($row['data'])) {
      $row['data'] = unserialize($row['data']);
    }

    return $row ?? [];
  }

  /**
   * Set the Payment Data.
   *
   * @param int $cart_id
   *   Cart ID.
   * @param string $unique_id
   *   Payment token id.
   * @param array $data
   *   Payment data.
   *
   * @throws \Doctrine\DBAL\DBALException
   * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
   */
  public function setPaymentData(int $cart_id, string $unique_id, array $data) {
    $this->connection->delete(self::TABLE_NAME, ['cart_id' => $cart_id]);
    $this->connection->insert(self::TABLE_NAME, [
      'cart_id' => $cart_id,
      'unique_id' => $unique_id,
      'data' => serialize($data),
      'timestamp' => (int) $_SERVER['REQUEST_TIME'],
    ]);
  }

}
