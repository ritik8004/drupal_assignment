<?php

namespace Drupal\acq_sku;

use Drupal\Core\Database\Connection;
use Drupal\acq_commerce\I18nHelper;
use Drupal\Core\Database\IntegrityConstraintViolationException;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Class ConductorCategorySyncHelper to sync categories.
 *
 * @package Drupal\acq_sku
 */
class ConductorCategorySyncHelper {

  /**
   * The database table name.
   */
  public const TABLE_NAME = 'category_sync_process';

  /**
   * Contains category list from DB which need to process.
   *
   * @var array
   */
  protected $catsToProcess = [];

  /**
   * Contains category data which needs to update.
   *
   * @var array
   */
  protected $catsToSync = [];

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Conductor category manager.
   *
   * @var \Drupal\acq_sku\ConductorCategoryManager
   */
  protected $conductorCategoryManager;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  protected $i18nHelper;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * ConductorCategorySyncHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\acq_sku\ConductorCategoryManager $conductor_category_manager
   *   Conductor category manager.
   * @param \Drupal\acq_commerce\I18nHelper $i18_helper
   *   I18 manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   Logger factory.
   */
  public function __construct(Connection $connection,
                              ConductorCategoryManager $conductor_category_manager,
                              I18nHelper $i18_helper,
                              LoggerChannelFactory $logger_factory) {
    $this->connection = $connection;
    $this->conductorCategoryManager = $conductor_category_manager;
    $this->i18nHelper = $i18_helper;
    $this->logger = $logger_factory->get('acq_sku');
  }

  /**
   * Adds category id in the table if already not.
   *
   * @param int $category_id
   *   ACM category id.
   *
   * @return bool
   *   True on success.
   */
  public function createItem($category_id) {
    try {
      $query = $this->connection->insert(static::TABLE_NAME)
        ->fields([
          'category_acm_id' => $category_id,
        ]);
      $query->execute();
      return TRUE;
    }
    catch (IntegrityConstraintViolationException) {
      $this->logger->notice('Not pushed cat: @cat_id in DB as already exists.', [
        '@cat_id' => $category_id,
      ]);
      // Assume this is because we have violated the uniqueness constraint.
      // Return FALSE to indicate that no item has been added.
      return FALSE;
    }
  }

  /**
   * Process the category sync for push.
   *
   * Use this function in script/cron to update/sync categories
   * after push from the magento system.
   *
   * Use something like -
   *   `drush php-eval '\Drupal::service("acq_sku.conductor_cat_sync_helper")->processCatSync()'`
   */
  public function processCatSync() {
    $this->catsToProcess = $this->connection->select(static::TABLE_NAME, 'tt')
      ->fields('tt', ['category_acm_id'])
      ->execute()
      ->fetchCol();

    // If no category, no need to process.
    if (empty($this->catsToProcess)) {
      $this->logger->notice('No category to process.');
      return;
    }

    try {
      // Delete those ids from table.
      $this->connection->delete(static::TABLE_NAME)
        ->condition('category_acm_id', $this->catsToProcess, 'IN')
        ->execute();

      foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
        // As we sync/update data for multiple stores, reset class variable
        // for next iteration and filling fresh data.
        $this->catsToSync = [];
        if ($store_id) {
          // Load category data.
          $categories = [$this->conductorCategoryManager->loadCategoryData($langcode)];
          $this->iterateRecursive($categories);

          // If no data to sync.
          if (empty($this->catsToSync)) {
            $this->logger->notice('No category data to sync.');
            continue;
          }

          foreach ($this->catsToSync as $cat_to_sync) {
            $this->logger->notice('Processing category tree for category: @acm_cat_id and for store: @store', [
              '@acm_cat_id' => $cat_to_sync['category_id'],
              '@store' => $langcode,
            ]);

            // Update the category in drupal.
            $this->conductorCategoryManager->synchronizeCategory('acq_product_category', $cat_to_sync);
          }
        }
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Error while syncing categories for pull mode for categories: @cat_acm_ids Message: @message', [
        '@cat_acm_ids' => json_encode($this->catsToProcess, JSON_THROW_ON_ERROR),
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Prepare the data of category that we need to sync.
   *
   * @param array $categories
   *   Category array.
   */
  public function iterateRecursive(array $categories) {
    foreach ($categories as $category) {
      // If category we are trying to process exists in sync data.
      if (in_array($category['category_id'], $this->catsToProcess)) {
        $this->catsToSync[$category['category_id']] = $category;
      }

      // If there are child items in category and parent
      // is not in sync list, only then process for children.
      if (!empty($category['children'])
        && !isset($this->catsToSync[$category['category_id']])) {
        $this->iterateRecursive($category['children']);
      }
    }
  }

}
