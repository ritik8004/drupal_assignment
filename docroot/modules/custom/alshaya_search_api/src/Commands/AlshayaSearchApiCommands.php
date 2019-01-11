<?php

namespace Drupal\alshaya_search_api\Commands;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
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
   * AlshayaSearchApiCommands constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The Date Time service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(Connection $connection,
                              TimeInterface $date_time,
                              LoggerChannelInterface $logger) {
    $this->connection = $connection;
    $this->dateTime = $date_time;
    $this->setLogger($logger);
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
    $query = $this->connection->query("SELECT node.nid, node.langcode, item.item_id 
      FROM search_api_item item 
      LEFT JOIN node ON item.item_id LIKE CONCAT('%', node.nid, ':', node.langcode) 
      WHERE node.nid IS NULL AND node.type = :node_type", [
        ':node_type' => 'acq_product',
      ]
    );

    $item_ids = $query->fetchAll();
    $indexes = ['acquia_search_index', 'product'];
    $item_ids = array_column($item_ids, 'item_id');
    $this->deleteItems($indexes, $item_ids);

    // 2. Delete items from db index which are no longer available in system.
    $query = $this->connection->query("SELECT node.nid, node.langcode, item.item_id 
      FROM search_api_db_product item 
      LEFT JOIN node ON item.item_id LIKE CONCAT('%', node.nid, ':', node.langcode) 
      WHERE node.nid IS NULL AND node.type = :node_type", [
        ':node_type' => 'acq_product',
      ]
    );

    $item_ids = $query->fetchAll();
    $indexes = ['product'];
    $item_ids = array_column($item_ids, 'item_id');

    // First add entry in search_api_item if required.
    $this->addEntryToSearchApiItem($indexes, $item_ids);
    $this->deleteItems($indexes, $item_ids);

    // 3. Re-index items that are missing in DB index.
    $query = $this->connection->query("SELECT node.nid, node.langcode, item.item_id 
      FROM node 
      LEFT JOIN search_api_db_product item ON item.item_id LIKE CONCAT('%', node.nid, ':', node.langcode) 
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

    foreach ($indexes as $index_id) {
      $index = Index::load($index_id);
      $index->trackItemsInserted('entity:node', $item_ids);
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
