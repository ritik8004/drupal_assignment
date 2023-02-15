<?php

namespace Drupal\alshaya_search_api;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class Alshaya Search Api Data Helper.
 */
class AlshayaSearchApiDataHelper {

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaSearchApiDataHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(Connection $connection,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
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
    if (!AlshayaSearchApiHelper::isIndexEnabled('product')) {
      throw new \Exception('This is not supported if database index is disabled.');
    }

    try {
      $query = $this->connection->select('search_api_db_product_' . $field, $field);
      $query->addField($field, 'value');
      $query->condition('item_id', $item_id);
      $values = $query->execute()->fetchAllKeyed(0, 0);
    }
    catch (\Exception) {
      // Do nothing, we may have disabled indexes temporarily.
      $values = [];
    }

    return is_array($values) ? $values : [];
  }

  /**
   * Get all the products that have value available in the specified field.
   *
   * This is no longer supported but function is not removed to avoid issues
   * as it was used once in an update hook.
   */
  public function getProductsWithDataInField(): never {
    throw new \Exception('This is no longer supported.');
  }

}
