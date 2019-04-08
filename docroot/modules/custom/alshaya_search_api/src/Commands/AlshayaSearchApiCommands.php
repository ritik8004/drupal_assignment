<?php

namespace Drupal\alshaya_search_api\Commands;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\search_api\Entity\Index;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaSearchApiCommands.
 *
 * @package Drupal\alshaya_search_api\Commands
 */
class AlshayaSearchApiCommands extends DrushCommands {

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $dateTime;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * AlshayaSearchApiCommands constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The Date Time service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(Connection $connection,
                              TimeInterface $date_time,
                              LoggerChannelInterface $logger,
                              SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->connection = $connection;
    $this->dateTime = $date_time;
    $this->setLogger($logger);
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Correct index data.
   *
   * @command alshaya_search_api:correct-index-data
   *
   * @aliases correct-index-data
   */
  public function correctIndexData() {
    // 1. Delete items from search_api which are no longer available in system.
    $query = $this->connection->query("SELECT sai.item_id FROM search_api_item sai
      LEFT JOIN node n ON n.nid = SUBSTR(SUBSTR(sai.item_id, 1, LENGTH(sai.item_id) - 3), 13)
      WHERE n.nid IS NULL");

    $item_ids = $query->fetchAll();
    $indexes = ['acquia_search_index', 'product'];
    $item_ids = array_column($item_ids, 'item_id');
    $this->deleteItems($indexes, $item_ids);

    // 2. Delete items from db index which are no longer available in system.
    $query = $this->connection->query("SELECT item.item_id 
      FROM search_api_db_product item 
      LEFT JOIN node ON item.original_nid = node.nid
      WHERE node.nid IS NULL");

    $item_ids = $query->fetchAll();
    $indexes = ['product'];
    $item_ids = array_column($item_ids, 'item_id');

    // First add entry in search_api_item if required.
    $this->addEntryToSearchApiItem($indexes, $item_ids);
    $this->deleteItems($indexes, $item_ids);

    // 3. Re-index items that are missing in DB index.
    $query = $this->connection->query("SELECT node.nid, node.langcode
      FROM node
      LEFT JOIN search_api_item item ON 
        node.nid = SUBSTR(SUBSTR(item.item_id, 1, LENGTH(item.item_id) - 3), 13) 
        AND node.langcode = SUBSTR(item.item_id, -2, 2)
      WHERE item.item_id IS NULL AND node.type = :node_type", [
        ':node_type' => 'acq_product',
      ]
    );

    $data = $query->fetchAll();

    $item_ids = [];
    foreach ($data as $row) {
      $item_ids[] = $row->nid . ':' . $row->langcode;
    }

    $indexes = ['product'];
    $this->deleteItems($indexes, $item_ids);
    $this->indexItems($indexes, $item_ids);
  }

  /**
   * Correct index stock data.
   *
   * @command alshaya_search_api:correct-index-stock-data
   *
   * @aliases correct-index-stock-data
   */
  public function correctIndexStockData() {
    // Find all index entries for which sku is in stock but index data says OOS
    // OR sku is OOS and index data says in stock.
    $query = $this->connection->query("SELECT sku.sku, sku.langcode, db.nid, db.stock 
      FROM {acq_sku_field_data} sku 
      LEFT JOIN {search_api_db_product} db ON db.sku = sku.sku AND db.search_api_language = sku.langcode
      WHERE db.nid IS NOT NULL");

    // Above query will return all the products in system.
    $data = $query->fetchAll();

    if (empty($data)) {
      return;
    }

    $item_ids = [];
    foreach (array_chunk($data, 100) as $chunk) {
      foreach ($chunk as $row) {
        $sku = SKU::loadFromSku($row->sku);

        // Not able to load SKU, we will handle it separately.
        if (!($sku instanceof SKU)) {
          continue;
        }

        $is_in_stock = $this->skuManager->isProductInStock($sku);

        // Valid data checks.
        if (($is_in_stock && $row->stock == 2) || (!$is_in_stock && $row->stock != 2)) {
          continue;
        }

        $item_ids[] = $row->nid . ':' . $row->langcode;
      }

      $indexes = ['acquia_search_index', 'product'];
      $this->reIndexItems($indexes, $item_ids);

      $this->entityTypeManager->getStorage('acq_sku')->resetCache();
      drupal_static_reset();
    }
  }

  /**
   * Delete items from index.
   *
   * @param array $indexes
   *   Indexes for which entry needs to be added in search api item.
   * @param array $item_ids
   *   Item ids.
   */
  private function deleteItems(array $indexes, array $item_ids) {
    if (empty($item_ids)) {
      return;
    }

    $item_ids = array_map(function ($a) {
      return str_replace('entity:node/', '', $a);
    }, $item_ids);

    $this->logger->warning(dt('Deleting items from index @items', [
      '@items' => json_encode($item_ids),
    ]));

    foreach ($indexes as $index_id) {
      $index = Index::load($index_id);
      $index->trackItemsDeleted('entity:node', $item_ids);
    }
  }

  /**
   * Mark items for indexation.
   *
   * @param array $indexes
   *   Indexes for which entry needs to be added in search api item.
   * @param array $item_ids
   *   Item ids.
   */
  private function indexItems(array $indexes, array $item_ids) {
    if (empty($item_ids)) {
      return;
    }

    $this->logger->warning(dt('Indexing items @items', [
      '@items' => json_encode($item_ids),
    ]));

    foreach ($indexes as $index_id) {
      $index = Index::load($index_id);
      $index->trackItemsInserted('entity:node', $item_ids);
    }
  }

  /**
   * Mark items for re-indexation.
   *
   * @param array $indexes
   *   Indexes for which entry needs to be added in search api item.
   * @param array $item_ids
   *   Item ids.
   */
  private function reIndexItems(array $indexes, array $item_ids) {
    if (empty($item_ids)) {
      return;
    }

    $this->logger->warning(dt('Re-indexing items @items', [
      '@items' => json_encode($item_ids),
    ]));

    foreach ($indexes as $index_id) {
      $index = Index::load($index_id);
      $index->trackItemsUpdated('entity:node', $item_ids);
    }
  }

  /**
   * Add entries in search_api_item.
   *
   * @param array $indexes
   *   Indexes for which entry needs to be added in search api item.
   * @param array $item_ids
   *   Item ids.
   */
  private function addEntryToSearchApiItem(array $indexes, array $item_ids) {
    if (empty($item_ids)) {
      return;
    }

    $this->logger->warning(dt('Adding entries for search_api_item for items @items', [
      '@items' => json_encode($item_ids),
    ]));

    foreach ($item_ids as $item_id) {
      foreach ($indexes as $index) {
        try {
          $query = $this->connection->insert('search_api_item');

          $query->fields([
            'item_id' => $item_id,
            'index_id' => $index,
            'datasource' => 'entity:node',
            'status' => 1,
            'changed' => $this->getRequestedTime(),
          ]);

          $query->execute();
        }
        catch (\Exception $e) {
          // Do nothing.
        }
      }
    }
  }

  /**
   * Get requested time.
   *
   * @return int
   *   Requested time.
   */
  private function getRequestedTime() {
    static $time;

    if (empty($time)) {
      $time = $this->dateTime->getRequestTime();
    }

    return $time;
  }

}
