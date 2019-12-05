<?php

namespace Drupal\alshaya_search_api;

use Drupal\Core\Database\Connection;

/**
 * Class AlshayaSearchApiDataHelper.
 */
class AlshayaSearchApiDataHelper {

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * AlshayaSearchApiDataHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Wrapper function to get indexed data.
   *
   * @param string $item_id
   *   Item ID.
   * @param string $field
   *   Field to get data for.
   *
   * @return array
   *   Category IDs indexed for the item.
   */
  public function getIndexedData(string $item_id, string $field): array {
    try {
      $query = $this->connection->select('search_api_db_product_' . $field, $field);
      $query->addField($field, 'value');
      $query->condition('item_id', $item_id);
      $values = $query->execute()->fetchAllKeyed(0, 0);
    }
    catch (\Exception $e) {
      // Do nothing, we may have disabled indexes temporarily.
      $values = [];
    }

    return is_array($values) ? $values : [];
  }

}
