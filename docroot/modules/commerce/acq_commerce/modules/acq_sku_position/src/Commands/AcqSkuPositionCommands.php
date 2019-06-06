<?php

namespace Drupal\acq_sku_position\Commands;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Insert;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\acq_sku\CategoryManagerInterface;
use Drupal\acq_commerce\I18nHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * Class AcqSkuPositionCommands.
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
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Category manager.
   *
   * @var \Drupal\acq_sku\CategoryManagerInterface
   */
  private $categoryManager;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Counter for inserted items.
   *
   * @var int
   */
  private $inserted;

  /**
   * Counter for updated items.
   *
   * @var int
   */
  private $updated;

  /**
   * Counter for skipped items.
   *
   * @var int
   */
  private $skipped;

  /**
   * Counter for skipped items.
   *
   * @var int
   */
  private $deleted;

  /**
   * AcqSkuPositionCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Looger Factory.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   Commerce Api Wrapper.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service.
   * @param \Drupal\acq_sku\CategoryManagerInterface $categoryManager
   *   Category manager.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(LoggerChannelFactoryInterface $logger,
                              APIWrapper $api_wrapper,
                              Connection $connection,
                              ModuleHandlerInterface $moduleHandler,
                              CategoryManagerInterface $categoryManager,
                              I18nHelper $i18n_helper,
                              ConfigFactoryInterface $configFactory) {
    $this->logger = $logger->get('acq_sku_position');
    $this->apiWrapper = $api_wrapper;
    $this->connection = $connection;
    $this->moduleHandler = $moduleHandler;
    $this->categoryManager = $categoryManager;
    $this->i18nHelper = $i18n_helper;
    $this->configFactory = $configFactory;
    parent::__construct();
  }

  /**
   * Drush command to sync sku product position based on category.
   *
   * @param string $position_type
   *   Name of the position type.
   * @param array $options
   *   Command options.
   *
   * @command acq_sku_position:position-sync
   *
   * @option category-source
   *   Source from where category to be fetched and run position sync.
   *
   * @aliases aapps,position-sync
   *
   * @usage drush aapps
   *   Sync product position based on category, by default "position".
   * @usage drush aapps myargument
   *   Sync product position based on category, by "myargument".
   * @usage drush aapps myargument --category-source=magento
   *   Sync product position with magento as source of categories.
   *
   * @throws \Drush\Exceptions\UserAbortException
   */
  public function syncPositions($position_type = 'position', array $options = ['category-source' => 'magento']) {
    $this->logger->notice('Product position sync in progress...');

    // If invalid option for category source.
    if (!in_array($options['category-source'], ['drupal', 'magento'])) {
      $this->io()->error(dt('Invalid category-source. It can only be `magento` or `drupal`'));
      throw new UserAbortException();
    }

    // Get category data from `drupal` or `magento` as per source option.
    $terms = $options['category-source'] == 'drupal'
      ? $this->getCategoriesFromDrupal()
      : $this->getCategoriesFromMagento();

    // Allow other modules to skip terms from position sync.
    $this->moduleHandler->alter('acq_sku_position_sync', $terms);
    $chunk_size = 100;

    $this->skipped = $this->updated = $this->inserted = 0;
    foreach (array_chunk($terms, $chunk_size) as $categories_chunk) {
      // Initialize insert query & add values for insert while processing the
      // API response for bulk insert.
      $insert_query = $this->connection->insert('acq_sku_position')
        ->fields(['nid', 'tid', 'position', 'position_type']);
      foreach ($categories_chunk as $term) {
        // Find the commerce id from the term. Skip if not found.
        $commerce_id = $term->commerce_id;
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
          $this->logger->error(dt('Exception while fetching position for category @name (tid: @tid). The category probably does not exist in commerce backend', [
            '@name' => $term->name,
            '@tid' => $term->tid,
          ]));
          continue;
        }

        // Skip sync if error found in the response for a particular category.
        if (is_array($response) && isset($response['message'])) {
          $this->logger->error(dt('Error in position sync for @name (tid: @tid). Response: @message', [
            '@name' => $term->name,
            '@tid' => $term->tid,
            '@message' => $response['message'],
          ]));
          continue;
        }

        // Start product position sync for this category.
        $this->logger->notice(dt('Product position sync for !name (tid: !tid) in progress...', [
          '!name' => $term->name,
          '!tid' => $term->tid,
        ]));

        // Get all skus from the response.
        $skus = array_column($response, 'sku');
        if (empty($skus)) {
          continue;
        }
        $nids = $this->fetchNidsForSkus($skus);

        // Skip if not product found for any sku.
        if (empty($nids)) {
          continue;
        }

        try {
          // Perform merge query for all positions received as a part of
          // position sync.
          $this->processPositionResponse($response, $term, $nids, $position_type, $insert_query);
        }
        catch (\Exception $e) {
          $this->logger->error('Error while updating data for product position for terms: @tids. Message: @message', [
            '@tids' => implode(',', array_column($categories_chunk, 'tid')),
            '@message' => $e->getMessage(),
          ]);
        }
      }

      try {
        // Execute query only if we have records to insert.
        if ($insert_record_count = $insert_query->count()) {
          $insert_query->execute();
          $this->inserted += $insert_record_count;
        }
      }
      catch (\Exception $e) {
        $this->logger->error('Error while inserting data for product position for terms: @tids. Message: @message', [
          '@tids' => implode(',', array_column($categories_chunk, 'tid')),
          '@message' => $e->getMessage(),
        ]);
      }

      $this->moduleHandler->invokeAll('acq_sku_position_sync_category_chunk_processed', $categories_chunk);
    }

    // Allow other modules to take action after position sync finished.
    $this->moduleHandler->invokeAll('acq_sku_position_sync_finished');

    $this->logger->notice(dt('Product position sync completed!'));

    $this->logger()->notice(dt('Inserted @inserted_count new positions, Updated @updated_count positions, Skipped @skipped_count positions, Deleted @deleted_count positions.', [
      '@inserted_count' => $this->inserted,
      '@updated_count' => $this->updated,
      '@skipped_count' => $this->skipped,
      '@deleted_count' => $this->deleted,
    ]));
  }

  /**
   * Helper Function to process response & prepare insert query.
   *
   * @param array $response
   *   Response received from Magento for positions.
   * @param \stdClass $term
   *   Term being processed.
   * @param array $nids
   *   List of nids fetched from list of SKUs in response for the term.
   * @param string $position_type
   *   Position type.
   * @param \Drupal\Core\Database\Query\Insert $insert_query
   *   Insert query object.
   *
   * @throws \Exception
   */
  public function processPositionResponse(array $response,
                                          \stdClass $term,
                                          array $nids,
                                          $position_type,
                                          Insert $insert_query) {
    // Fetch positions from database.
    $db_positions = $this->getDbPositions();
    $processed_response_nids = [];

    foreach ($response as $product_position) {
      // Check if the position has changed before doing a sync.
      if (isset($nids[$product_position['sku']])) {
        // Prepare list of valid nids from the response for deleting the invalid
        // ones.
        $processed_response_nids[] = $nids[$product_position['sku']];
        $db_position_nid = isset($db_positions[$term->tid][$nids[$product_position['sku']]]) ? $db_positions[$term->tid][$nids[$product_position['sku']]] : NULL;

        // Skip merge query for this if the db position matches the one in
        // response.
        if (($db_position_nid !== NULL) &&
          ($db_position_nid == $product_position['position'])) {
          $this->skipped++;
          continue;
        }

        // If we have a position for this nid in DB, over here it would mean
        // it needs an update.
        if (!empty($db_position_nid)) {
          // Update new position data for the product.
          $this->connection->update('acq_sku_position')
            ->fields(['position' => $product_position['position']])
            ->condition('tid', $term->tid)
            ->condition('nid', $nids[$product_position['sku']])
            ->condition('position_type', $position_type)
            ->execute();
          $this->updated++;
        }
        // Prepare insert query if no matching record found in DB.
        elseif ($db_position_nid === NULL) {
          $insert_record = [
            'nid' => $nids[$product_position['sku']],
            'tid' => $term->tid,
            'position_type' => $position_type,
            'position' => $product_position['position'],
          ];

          $insert_query->values($insert_record);
        }
      }
    }

    $this->removeObsoletePositionsForCategory($processed_response_nids, $term);
  }

  /**
   * Remove position for SKUs which we don't receive from position API.
   *
   * @param array $processed_response_nids
   *   List of nids from response which were processed for the current term.
   * @param \stdClass $term
   *   Term object which is being processed.
   */
  public function removeObsoletePositionsForCategory(array $processed_response_nids, \stdClass $term) {
    $db_positions = $this->getDbPositions();

    // Return if there was no record in db for this term pre-import.
    if (empty($db_positions[$term->tid])) {
      return;
    }

    // Fetch the diff between nids in DB & the ones from response.
    $obsolete_record_nids = array_diff(array_keys($db_positions[$term->tid]), $processed_response_nids);

    // Delete obsolete records & update the delete count for logging.
    if (!empty($obsolete_record_nids)) {
      $deleted_count = $this->connection->delete('acq_sku_position')
        ->condition('tid', $term->tid)
        ->condition('nid', $obsolete_record_nids, 'IN')
        ->execute();
      $this->deleted += $deleted_count;
    }
  }

  /**
   * Helper function to fetch node ids based on the SKUs.
   *
   * @param array $skus
   *   List of SKUs for which nids need to be fetched.
   *
   * @return array
   *   List of nids corresponding to SKUs.
   */
  public function fetchNidsForSkus(array $skus) {
    // Get all product nids from skus.
    $query = $this->connection->select('node__field_skus', 'n');
    $query->fields('n', ['field_skus_value', 'entity_id']);
    $query->condition('n.bundle', 'acq_product');
    $query->condition('n.field_skus_value', $skus, 'IN');
    $nids = $query->execute()->fetchAllKeyed();

    return $nids;
  }

  /**
   * Helper function to fetch positions stored in DB for the category chunk.
   *
   * @return array
   *   List of product positions grouped by term_id.
   */
  public function getDbPositions() {
    $db_positions = &drupal_static(__METHOD__, []);

    if (!empty($db_positions)) {
      return $db_positions;
    }

    $query = $this->connection->select('acq_sku_position', 'asp');
    $query->fields('asp', ['nid', 'tid', 'position']);
    $existing_nid_positions = $query->execute()->fetchAll();
    $db_positions = [];

    // Group product position mapping by tids.
    foreach ($existing_nid_positions as $position) {
      if (!isset($db_positions[$position->tid])) {
        $db_positions[$position->tid] = [];
      }
      $db_positions[$position->tid][$position->nid] = $position->position;
    }

    return $db_positions;
  }

  /**
   * Fetch categories data from Magento for position sync.
   *
   * @return array
   *   Categories data.
   */
  protected function getCategoriesFromMagento() {
    $cat_data = [];
    // Get all terms data from drupal.
    $drupal_cat_data = $this->getCategoriesFromDrupal();
    foreach ($this->i18nHelper->getStoreLanguageMapping() as $store_id) {
      if ($store_id) {
        // Load Conductor Category data.
        $categories = [$this->categoryManager->loadCategoryData($store_id)];
        // Filter/Remove top level root category.
        $filter_root_category = $this->configFactory->get('acq_commerce.conductor')->get('filter_root_category');
        if ($filter_root_category && !empty($categories)) {
          $categories = $categories[0]['children'];
        }

        // Recursively prepare category data from children.
        // @codingStandardsIgnoreLine giving warning for anonymous function.
        $cat_recur_data = function ($cats) use (&$cat_recur_data, &$cat_data, $drupal_cat_data) {
          foreach ($cats as $cat) {
            if (!isset($cat['category_id']) || empty($cat['name'])) {
              continue;
            }

            // If langcode not available, means no mapping of store and
            // language.
            if (!$this->i18nHelper->getLangcodeFromStoreId($cat['store_id'])) {
              continue;
            }

            // If term exists in drupal, only then we consider or we won't have
            // the term id and thus no reason to sync position.
            if (isset($drupal_cat_data[$cat['category_id']])) {
              $cat_data[$cat['category_id']] = (object) [
                'commerce_id' => $cat['category_id'],
                'name' => $drupal_cat_data[$cat['category_id']]->name,
                'tid' => $drupal_cat_data[$cat['category_id']]->tid,
              ];
            }

            // If there are children for the term.
            if (!empty($cat['children'])) {
              // @codingStandardsIgnoreLine giving warning for anonymous function.
              $cat_recur_data($cat['children']);
            }
          }
        };

        $cat_recur_data($categories);
      }
    }

    return $cat_data;
  }

  /**
   * Fetch categories data from Drupal for position sync.
   *
   * @return array
   *   Categories data.
   */
  protected function getCategoriesFromDrupal() {
    $sql_query = 'SELECT tc.entity_id as tid, tc.field_commerce_id_value as commerce_id, td.name
                  FROM taxonomy_term__field_commerce_id tc
                  INNER JOIN taxonomy_term_field_data td
                  ON td.tid=tc.entity_id AND td.langcode=tc.langcode';

    // Get all product category terms.
    $query = $this->connection->query($sql_query);
    $query->execute();
    return $query->fetchAllAssoc('commerce_id');
  }

}
