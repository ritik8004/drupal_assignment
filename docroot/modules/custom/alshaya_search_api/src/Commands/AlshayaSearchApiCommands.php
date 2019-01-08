<?php

namespace Drupal\alshaya_search_api\Commands;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelInterface;
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
    $query = $this->connection->query('SELECT product.item_id FROM {search_api_db_product} product 
      LEFT JOIN {search_api_item} item ON item.item_id = product.item_id 
      WHERE item.item_id IS NULL');

    $item_ids = $query->fetchAll();

    if (empty($item_ids)) {
      $this->say('Every-thing good.');
      return;
    }

    $indexes = ['acquia_search_index', 'product'];
    $requested_time = $this->dateTime->getRequestTime();

    $item_ids = array_column($item_ids, 'item_id');

    foreach ($item_ids as $item_id) {
      foreach ($indexes as $index) {
        try {
          $query = $this->connection->insert('search_api_item');

          $query->fields([
            'item_id' => $item_id,
            'index_id' => $index,
            'datasource' => 'entity:node',
            'status' => 1,
            'changed' => $requested_time,
          ]);

          $query->execute();
        }
        catch (\Exception $e) {
          // Entry may exist, doing nothing here.
          $this->io()->writeln('Failed to insert for: ' . $index . ' : ' . $item_id);
          $this->logger->info('Failed to insert for: @index : @item_id', [
            '@index' => $index,
            '@item_id' => $item_id,
          ]);
        }
      }

      $this->io()->writeln('Index data corrected for: ' . $item_id);
      $this->logger->info('Index data corrected for: @item_id', [
        '@item_id' => $item_id,
      ]);
    }
  }

}
