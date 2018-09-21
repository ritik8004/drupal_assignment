<?php
namespace Drupal\acq_sku_position\Commands;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Class AcqSkuPositionCommands
 *
 * @package Drupal\acq_sku_position\Commands
 */
class AcqSkuPositionCommands extends DrushCommands {

  /**
   * Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * Database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Commerce Api Wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  private $apiWrapper;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * AcqSkuPositionCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Looger Factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $APIWrapper
   *   Commerce Api Wrapper.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service.
   */
  public function __construct(LoggerChannelFactoryInterface $logger,
                              EntityTypeManagerInterface $entityTypeManager,
                              APIWrapper $APIWrapper,
                              Connection $connection,
                              ModuleHandlerInterface $moduleHandler) {
    $this->logger = $logger->get('acq_sku_position_position');
    $this->entityTypeManager = $entityTypeManager;
    $this->apiWrapper = $APIWrapper;
    $this->connection = $connection;
    $this->moduleHandler = $moduleHandler;
  }


  /**
   * Drush command to sync sku product position based on category.
   *
   * @command acq_sku_position:position-sync
   *
   * @param string $position_type
   *   Name of the position type.
   *
   * @aliases aapps,position-sync
   *
   * @usage drush aapps
   *   Sync product position based on category, by default "position".
   * @usage drush aapps myargument
   *   Sync product position based on category, by "myargument".
   *
   */
  function syncPositions($position_type = 'position') {
    $this->logger->notice('Product position sync in progress...');

    // Get all product category terms.
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('acq_product_category');
    foreach ($terms as $term) {
      // Find the commerce id from the term. Skip if not found.
      $commerce_id = $this->entityTypeManager->getStorage('taxonomy_term')->load($term->tid)->get('field_commerce_id')->value;
      if (!$commerce_id) {
        continue;
      }

      // Get product position for this category from commerce backend.
      try {
        $response = $this->apiWrapper->getProductPosition($commerce_id);
        if (!is_array($response)) {
          continue;
        }
      }
      catch (\Exception $e) {
        $this->logger->error('Exception while fetching position for category @name (tid: @tid). The category probably does not exist in commerce backend', [
          '@name' => $term->name,
          '@tid' => $term->tid,
        ]);
        continue;
      }

      // Skip sync if error is found in the response for a particular category.
      if (is_array($response) && isset($response['message'])) {
        $this->logger->error('Error in position sync for @name (tid: @tid). Response: @message', [
          '@name' => $term->name,
          '@tid' => $term->tid,
          '@message' => $response['message'],
        ]);
        continue;
      }

      // Start product position sync for this category.
      $this->logger->notice('Product position sync for @name (tid: @tid) in progress...', [
        '@name' => $term->name,
        '@tid' => $term->tid,
      ]);

      // Delete existing records of position for this category.
      $this->connection->delete('acq_sku_position')
        ->condition('tid', $term->tid)
        ->condition('position_type', $position_type)
        ->execute();

      // Get all skus from the response.
      $skus = array_column($response, 'sku');
      if (empty($skus)) {
        continue;
      }

      // Get all product nids from skus.
      $query = $this->connection->select('node__field_skus', 'n');
      $query->fields('n', ['field_skus_value', 'entity_id']);
      $query->condition('n.bundle', 'acq_product');
      $query->condition('n.field_skus_value', $skus, 'IN');
      $nids = $query->execute()->fetchAllKeyed();
      // Skip if not product found for any sku.
      if (empty($nids)) {
        continue;
      }

      $is_data_available = FALSE;
      $query = $this->connection->insert('acq_sku_position')
        ->fields(['nid', 'tid', 'position', 'position_type']);
      foreach ($response as $product_position) {
        if (isset($nids[$product_position['sku']])) {
          // Insert new position data for the product.
          $record = [
            'nid' => $nids[$product_position['sku']],
            'tid' => $term->tid,
            'position' => $product_position['position'],
            'position_type' => $position_type,
          ];
          $is_data_available = TRUE;
          $query->values($record);
        }
      }

      if ($is_data_available) {
        $query->execute();
      }
    }

    // Allow other modules to take action after position sync finished.
    $this->moduleHandler->invokeAll('acq_sku_position_sync_finished');

    $this->logger->notice('Product position sync completed!');
  }

}
