<?php

namespace Drupal\alshaya_rcs_product\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class to help with migration of acq product to rcs product nodes.
 */
class AlshayaRcsProductMigrationCommands extends DrushCommands {

  /**
   * Entity query for node.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $nodeQuery;

  /**
   * Entity query for sku.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $skuQuery;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * AlshayaRcsProductCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger channel factory.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->nodeQuery = $entity_type_manager->getStorage('node')->getQuery();
    $this->skuQuery = $entity_type_manager->getStorage('acq_sku')->getQuery();
    $this->drupalLogger = $logger_factory->get('alshaya_rcs_product');
  }

  /**
   * Batch operation for deleting acq product nodes.
   *
   * @param array $entity_ids
   *   Node ids.
   * @param int $count
   *   Number of nodes to delete.
   * @param string $entity_type
   *   The type of entity to delete.
   */
  public static function deleteEntities(
    array $entity_ids,
    int $count,
    string $entity_type
  ) {
    $context = [];
    // Initialized node count to zero.
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $count;
    }

    /** @var \Drupal\Core\Entity\EntityStorageInterface */
    $entity_storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $entities = $entity_storage->loadMultiple($entity_ids);
    $entity_storage->delete($entities);
    $context['sandbox']['progress']++;

    if ($context['sandbox']['progress'] !== $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Batch finish operation for deletion of entities.
   */
  public static function deleteEntitiesFinished($success, $results, $operations) {
    if ($success) {
      $message = 'Batch processs completed successfully.';
    }
    else {
      $error_operation = reset($operations);
      $message = dt('An error occurred while processing %error_operation.', [
        '%error_operation' => $error_operation[0],
      ]);
    }
    \Drupal::logger('alshaya_rcs_product')->notice($message);
  }

  /**
   * Deletes all acq product nodes from the system.
   *
   * @command alshaya_rcs_product:delete-acq-product-nodes
   * @aliases arpdelproductnodes,rcs-delete-product-nodes
   *
   * @usage alshaya_rcs_product:delete-acq-product_nodes
   *   Deletes all acq product nodes.
   * @usage alshaya_rcs_product:delete-acq-product_nodes --batch-size 50
   *   Deletes all acq product nodes and sets batch size to 50.
   */
  public function deleteAcqProductNodesBatch(array $options = ['batch-size' => NULL]) {
    $this->drupalLogger->notice('Starting batch process to delete acq product nodes.');
    // Delete all acq_product nodes from the system.
    $acq_product_nids = $this->nodeQuery
      ->condition('type', 'acq_product')
      ->execute();
    // Delete all product nodes from the system.
    $nodes_to_delete = is_countable($acq_product_nids) ? count($acq_product_nids) : 0;

    if (!$nodes_to_delete) {
      $this->drupalLogger->notice('There are no product nodes to delete! Exiting!');
      return;
    }

    $this->drupalLogger->notice(dt('There are @count product nodes to delete!', [
      '@count' => $nodes_to_delete,
    ]));

    $batch = [
      'title' => 'Delete acq product nodes',
      'finished' => [self::class, 'deleteEntitiesFinished'],
    ];

    $batch_size = $options['batch-size'] ?? 20;
    foreach (array_chunk($acq_product_nids, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [self::class, 'deleteEntities'],
        [
          $chunk,
          is_countable($acq_product_nids) ? count($acq_product_nids) : 0, 'node',
        ],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();
  }

  /**
   * Deletes all acq product nodes from the system.
   *
   * @command alshaya_rcs_product:delete-acq-sku
   * @aliases arpdelacqsku,rcs-delete-product-skus
   *
   * @usage alshaya_rcs_product:delete-acq-sku
   *   Deletes all acq sku entities.
   * @usage alshaya_rcs_product:delete-acq-sku --batch-size 50
   *   Deletes all acq sku entities and sets batch size to 50.
   */
  public function deleteAcqSkusBatch(array $options = ['batch-size' => NULL]) {
    $this->drupalLogger->notice('Starting batch process to delete acq sku entities.');
    // Delete all acq_sku nodes from the system.
    $acq_sku_ids = $this->skuQuery->execute();
    // Delete all product nodes from the system.
    $skus_to_delete = is_countable($acq_sku_ids) ? count($acq_sku_ids) : 0;

    if (!$skus_to_delete) {
      $this->drupalLogger->notice('There are no sku entities to delete! Exiting!');
      return;
    }

    $this->drupalLogger->notice(dt('There are @count sku entities to delete!', [
      '@count' => $skus_to_delete,
    ]));

    $batch = [
      'title' => 'Delete acq sku entities',
      'finished' => [self::class, 'deleteEntitiesFinished'],
    ];

    $batch_size = $options['batch-size'] ?? 20;
    foreach (array_chunk($acq_sku_ids, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [self::class, 'deleteEntities'],
        [
          $chunk,
          is_countable($acq_sku_ids) ? count($acq_sku_ids) : 0, 'acq_sku',
        ],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();
  }

}
