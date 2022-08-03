<?php

namespace Drupal\alshaya_rcs_main_menu\Commands;

use Drush\Commands\DrushCommands;
use Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryDataMigration;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Alshaya RCS Category Migrate Commands class.
 */
class AlshayaRcsCategoryMigrationCommands extends DrushCommands {

  /**
   * Entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $entityQuery;

  /**
   * Term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * AlshayaRcsCategoryCommands constructor.
   *
   * @param \Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryDataMigration $alshaya_category_migrate
   *   RCS Category Migrate Service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger channel factory.
   */
  public function __construct(
    AlshayaRcsCategoryDataMigration $alshaya_category_migrate,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->alshayaCategoryMigrate = $alshaya_category_migrate;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->entityQuery = $this->termStorage->getQuery();
    $this->drupalLogger = $logger_factory->get('alshaya_rcs_listing');
  }

  /**
   * Migrate RCS Category terms.
   *
   * @command alshaya-rcs-category:migrate
   *
   * @aliases arcm,arc-migrate,rcs-category-migrate
   *
   * @options batch_size
   *   The number of rcs category to migrate per batch.
   *
   * @usage drush arcm --batch_size=30
   *   Create Enriched RCS Category terms from Product Category.
   */
  public function migrateRcsCategory($options = ['batch_size' => 50]) {
    // Set rcs category migrate batch.
    $this->alshayaCategoryMigrate->processProductCategoryMigration($options['batch_size']);
    $this->drupalLogger->notice(dt('RCS Category migration completed.'));
  }

  /**
   * Reinstate ACQ Product Category terms.
   *
   * @command alshaya-acq-category:migrate
   *
   * @aliases acqm,acq-migrate
   *
   * @options batch_size
   *   The number of acq category to migrate per batch.
   *
   * @usage drush acqm --batch_size=30
   *   Reinstate Enriched Product Category from RCS Category terms.
   */
  public function reinstateAcqTerms($options = ['batch_size' => 50]) {
    // Set acq product category migrate batch.
    $this->alshayaCategoryMigrate->rollbackProductCategoryMigration($options['batch_size']);
    $this->drupalLogger->notice('ACQ Category terms reinstated.');
  }

  /**
   * Batch operation for deleting product category terms.
   *
   * @param array $tids
   *   Term ids.
   * @param int $count
   *   Number of terms to delete.
   */
  public static function deleteAcqProductCategoryTerms(array $tids, $count) {
    $context = [];
    // Initialized term count to zero.
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $count;
    }

    /** @var \Drupal\taxonomy\TermStorageInterface */
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $terms = $term_storage->loadMultiple($tids);
    $term_storage->delete($terms);
    $context['sandbox']['progress']++;

    if ($context['sandbox']['progress'] !== $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Batch finish operation for deletion of acq product category terms.
   */
  public static function acqProductCategoryTermsDeletionFinished($success, $results, $operations) {
    if ($success) {
      $message = 'Batch processs completed successfully.';
    }
    else {
      $error_operation = reset($operations);
      $message = dt('An error occurred while processing %error_operation.', [
        '%error_operation' => $error_operation[0],
      ]);
    }
    \Drupal::logger('alshaya_rcs_listing')->notice($message);
  }

  /**
   * Deletes all acq product category terms from the system.
   *
   * @command alshaya_rcs_listing:delete-acq-product-category-terms
   * @aliases arldelterm,rcs-delete-category
   *
   * @usage alshaya_rcs_listing:delete-acq-product-category-terms
   *   Deletes all acq category terms.
   * @usage alshaya_rcs_listing:delete-acq-product-category-terms --batch-size 50
   *   Deletes all acq category terms and sets batch size to 50.
   */
  public function deleteAcqProductCategoryTermsBatch(array $options = ['batch-size' => NULL]) {
    $this->drupalLogger->notice('Starting batch process to delete acq category terms.');
    // Delete all product category terms from the system.
    $acq_product_category_tids = $this->entityQuery
      ->condition('vid', 'acq_product_category')
      ->execute();
    $terms_to_delete = is_countable($acq_product_category_tids) ? count($acq_product_category_tids) : 0;

    if (!$terms_to_delete) {
      $this->drupalLogger->notice('There are no terms to delete! Exiting!');
      return;
    }
    else {
      $this->drupalLogger->notice(dt('There are @count term entities to delete!', [
        '@count' => $terms_to_delete,
      ]));
    }

    $batch = [
      'title' => 'Delete product category terms',
      'finished' => [self::class, 'acqProductCategoryTermsDeletionFinished'],
    ];

    $batch_size = $options['batch-size'] ?? 20;
    foreach (array_chunk($acq_product_category_tids, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [self::class, 'deleteAcqProductCategoryTerms'],
        [
          $chunk,
          is_countable($acq_product_category_tids) ? count($acq_product_category_tids) : 0,
        ],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();
  }

}
