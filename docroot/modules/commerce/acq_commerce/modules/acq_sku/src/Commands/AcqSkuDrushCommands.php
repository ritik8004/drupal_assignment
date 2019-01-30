<?php

namespace Drupal\acq_sku\Commands;

use Drupal\acq_commerce\Conductor\APIWrapperInterface;
use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\ConductorCategoryManager;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\rest\resource\ProductSyncResource;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\taxonomy\TermInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * class AcqSkuDrushCommands
 */
class AcqSkuDrushCommands extends DrushCommands {

  const DELETE_BATCH_COUNT = 200;

  /**
   * Api Wrapper service.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapperInterface
   */
  private $apiWrapper;

  /**
   * I18nHelper service.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nhelper;

  /**
   * Ingest Api Wrapper service.
   *
   * @var \Drupal\acq_commerce\Conductor\IngestAPIWrapper
   */
  private $ingestApiWrapper;

  /**
   * Conductor category manager service.
   *
   * @var \Drupal\acq_sku\ConductorCategoryManager
   */
  private $conductorCategoryManager;

  /**
   * Product options manager service.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  private $productOptionsManager;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Query Factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  private $queryFactory;

  /**
   * Entity Manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * Linked SKU cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $linkedSkuCache;

  /**
   * Cache Tags Invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  private $cacheTagsInvalidator;

  /**
   * Stock Cache Handler.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $stockCache;

  /**
   * AcqSkuDrushCommands constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapperInterface $apiWrapper
   *   Commerce Api Wrapper.
   * @param \Drupal\acq_commerce\I18nHelper $i18nHelper
   *   i18nHelper service.
   * @param \Drupal\acq_commerce\Conductor\IngestAPIWrapper $ingestAPIWrapper
   *   Ingest Api Wrapper service.
   * @param \Drupal\acq_sku\ConductorCategoryManager $conductorCategoryManager
   *   Conductor category manager service.
   * @param \Drupal\acq_sku\ProductOptionsManager $productOptionsManager
   *   Product Options Manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Channel Factory service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\Query\QueryFactory $queryFactory
   *   Query Factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   Entity Manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $langaugeManager
   *   Language Manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $linkedSkuCache
   *   Cache Backend Service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $stockCache
   *   Stock Cache Handler.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   Cache Tags invalidator.
   */
  public function __construct(APIWrapperInterface $apiWrapper,
                              I18nHelper $i18nHelper,
                              IngestAPIWrapper $ingestAPIWrapper,
                              ConductorCategoryManager $conductorCategoryManager,
                              ProductOptionsManager $productOptionsManager,
                              LoggerChannelFactoryInterface $loggerChannelFactory,
                              Connection $connection,
                              EntityTypeManagerInterface $entityTypeManager,
                              QueryFactory $queryFactory,
                              EntityManagerInterface $entityManager,
                              LanguageManagerInterface $langaugeManager,
                              ModuleHandlerInterface $moduleHandler,
                              CacheBackendInterface $linkedSkuCache,
                              CacheBackendInterface $stockCache,
                              CacheTagsInvalidatorInterface $cacheTagsInvalidator) {
    parent::__construct();
    $this->apiWrapper = $apiWrapper;
    $this->i18nhelper = $i18nHelper;
    $this->ingestApiWrapper = $ingestAPIWrapper;
    $this->conductorCategoryManager = $conductorCategoryManager;
    $this->productOptionsManager = $productOptionsManager;
    $this->logger = $loggerChannelFactory->get('acq_sku');
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->queryFactory = $queryFactory;
    $this->entityManager = $entityManager;
    $this->languageManager = $langaugeManager;
    $this->moduleHandler = $moduleHandler;
    $this->linkedSkuCache = $linkedSkuCache;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->stockCache = $stockCache;
  }

  /**
   * Run a full synchronization of all commerce product records.
   *
   * @throws \Drush\Exceptions\UserAbortException
   *
   * @command acq_sku:sync-products
   *
   * @param string $langcode
   *   Sync products available in this langcode.
   * @param string $page_size
   *   Number of items to be synced in one batch.
   *
   * @param array $options
   *
   * @option skus SKUs to import (like query).
   * @option category_id Magento category id to sync the products for.
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases acsp,sync-commerce-products
   *
   * @usage drush acsp en 50
   *   Run a full product synchronization of all available products in store linked to en and page size 50.
   * @usage drush acsp en 50 --skus=\'M-H3495 130 2  FW\',\'M-H3496 130 004FW\',\'M-H3496 130 005FW\''
   *   Synchronize sku data for the skus M-H3495 130 2  FW, M-H3496 130 004FW & M-H3496 130 005FW only in store linked to en and page size 50.
   * @usage drush acsp en 50 --category_id=1234
   *   Synchronize sku data for the skus in category with id 1234 only in store linked to en and page size 50.
   */
  public function syncProducts($langcode, $page_size, $options = ['skus' => NULL, 'category_id' => NULL]) {
    $langcode = strtolower($langcode);

    $store_id = $this->i18nhelper->getStoreIdFromLangcode($langcode);

    if (empty($store_id)) {
      $this->output->writeln(dt("Store id not found for provided language code."));
      return;
    }

    $page_size = (int) $page_size;

    if ($page_size <= 0) {
      $this->output->writeln(dt("Page size must be a positive integer."));
      return;
    }

    $skus = $options['skus'];

    $category_id = $options['category_id'];

    // Apply only one filer at a time.
    if ($category_id) {
      $skus = '';
    }

    // Ask for confirmation from user if attempt is to run full sync.
    if (empty($skus) && empty($category_id)) {
      $confirmation_text = dt('I CONFIRM');
      $input = $this->io()->ask(dt('Are you sure you want to import all products for @language language? If yes, type: "@confirmation"', [
        '@language' => $langcode,
        '@confirmation' => $confirmation_text,
      ]));

      if ($input != $confirmation_text) {
        throw new UserAbortException();
      }
    }

    $this->output->writeln(dt('Requesting all commerce products for selected language code...'));
    $this->ingestApiWrapper->productFullSync($store_id, $langcode, $skus, $category_id, $page_size);
    $this->output->writeln(dt('Done.'));
  }

  /**
   * Run a full synchronization of all commerce product category records.
   *
   * @command acq_sku:sync-categories
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases acsc,sync-commerce-cats
   *
   * @usage drush acsc
   *   Run a full category synchronization of all available categories.
   */
  public function syncCategories() {
    $this->output->writeln(dt('Synchronizing all commerce categories, please wait...'));
    $response = $this->conductorCategoryManager->synchronizeTree('acq_product_category');

    // We trigger delete only if there is any term update/create.
    // So if API does not return anything, we don't delete all the categories.
    if (!empty($response['created']) || !empty($response['updated'])) {
      // Get all category terms with commerce id.
      $query = $this->connection->select('taxonomy_term_field_data', 'ttd');
      $query->fields('ttd', ['tid', 'name']);
      $query->leftJoin('taxonomy_term__field_commerce_id', 'tcid', 'ttd.tid=tcid.entity_id');
      $query->fields('tcid', ['field_commerce_id_value']);
      $query->condition('ttd.vid', 'acq_product_category');
      $result = $query->execute()->fetchAllAssoc('tid', \PDO::FETCH_ASSOC);

      $affected_terms = array_unique(array_merge($response['created'], $response['updated']));
      // Filter terms which are not in sync response.
      $result = array_filter($result, function ($val) use ($affected_terms) {
        return !in_array($val['field_commerce_id_value'], $affected_terms);
      });

      // If there are categories to delete.
      if (!empty($result)) {
        // Show `tid + cat name + commerce id` for review.
        $this->io()->table([dt('Category Id'), dt('Category Name'), dt('Category Commerce Id')], $result);
        // Confirmation to delete old categories.
        if ($this->io()->confirm(dt('Are you sure you want to clean these old categories'), FALSE)) {

          // Allow other modules to skipping the deleting of terms.
          $this->moduleHandler->alter('acq_sku_sync_categories_delete', $result);

          foreach ($result as $tid => $rs) {
            $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
            if ($term instanceof TermInterface) {
              // Delete the term.
              $term->delete();
            }
          }
        }
      }
    }
    else {
      $this->logger->notice(dt('Not cleaning(deleting) old terms as there is no term update/create.'));
    }

    $this->output->writeln(dt('Done.'));
  }

  /**
   * Run a full synchronization of all commerce product options.
   *
   * @command acq_sku:sync-product-options
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases acspo,sync-commerce-product-options
   */
  public function syncProductOptions() {
    $this->logger->notice(dt('Synchronizing all commerce product options, please wait...'));
    $this->productOptionsManager->synchronizeProductOptions();
    $this->logger->notice(dt('Product attribute sync completed.'));
  }

  /**
   * Run a partial synchronization of commerce product records synchronously for testing / dev.
   *
   * @command acq_sku:sync-products-test
   *
   * @param int $count
   *   Number of product records to sync.
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases acdsp,sync-commerce-products-test
   *
   * @usage drush acdsp
   *   Run a partial synchronization of commerce product records synchronously for testing / dev.
   */
  public function syncProductsTest($count) {
    $this->output->writeln(dt('Synchronizing @count commerce products for testing / dev...', ['@count' => $count]));

    $container = \Drupal::getContainer();
    foreach ($this->i18nhelper->getStoreLanguageMapping() as $langcode => $store_id) {
      $this->apiWrapper->updateStoreContext($store_id);

      $products = $this->apiWrapper->getProducts($count);
      $product_sync_resource = ProductSyncResource::create($container, [], NULL, NULL);
      $product_sync_resource->post($products);
    }
  }

  /**
   * Remove all duplicate categories available in system.
   *
   * @command acq_sku:remove-category-duplicates
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases acccrd,commerce-cats-remove-duplicates
   *
   * @usage drush acccrd
   *   Remove all duplicate categories available in system.
   */
  public function removeCategoryDuplicates() {
    $this->output->writeln(dt('Cleaning all commerce categories, please wait...'));

    $db = $this->connection;

    /** @var \Drupal\taxonomy\TermStorageInterface $termStorage */
    $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

    /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

    $query = $db->select('taxonomy_term__field_commerce_id', 'ttfci');
    $query->addField('ttfci', 'field_commerce_id_value', 'commerce_id');
    $query->groupBy('commerce_id');
    $query->having('count(*) > 1');
    $result = $query->execute()->fetchAllKeyed(0, 0);

    if (empty($result)) {
      $this->output->writeln(dt('No duplicate categories found.'));
      return;
    }

    foreach ($result as $commerce_id) {
      $this->output->writeln(dt('Duplicate categories found for commerce id: @commerce_id.', [
        '@commerce_id' => $commerce_id,
      ]));

      $query = $db->select('taxonomy_term__field_commerce_id', 'ttfci');
      $query->addField('ttfci', 'entity_id', 'tid');
      $query->condition('ttfci.field_commerce_id_value', $commerce_id);
      $query->orderBy('tid', 'DESC');
      $tids = $query->execute()->fetchAllKeyed(0, 0);

      foreach ($tids as $tid) {
        $query = $nodeStorage->getQuery();
        $query->condition('field_category', $tid);
        $nodes = $query->execute();

        if (empty($nodes)) {
          $this->output->writeln(dt('No nodes found for tid: @tid for commerce id: @commerce_id. Deleting', [
            '@commerce_id' => $commerce_id,
            '@tid' => $tid,
          ]));

          $term = $termStorage->load($tid);
          $term->delete();

          unset($tids[$tid]);

          // Break the loop if only one left now, we might not have any products
          // added yet and categories are synced which means there will be no
          // nodes for any term.
          if (count($tids) == 1) {
            break;
          }
        }
        else {
          $this->output->writeln(dt('@count nodes found for tid: @tid for commerce id: @commerce_id. Not Deleting', [
            '@commerce_id' => $commerce_id,
            '@tid' => $tid,
            '@count' => count($nodes),
          ]));
        }
      }
    }

    $this->output->writeln(dt('Done.'));
  }

  /**
   * Remove all duplicate products available in system.
   *
   * @command acq_sku:remove-product-duplicates
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases accprd,commerce-products-remove-duplicates
   *
   * @usage drush accprd
   *   Remove all duplicate products available in system.
   */
  public function removeProductDuplicates() {
    $this->output->writeln(dt('Removing duplicates in commerce products, please wait...'));

    $skus_to_sync = [];

    /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    $query = $this->connection->select('acq_sku_field_data', 't1');
    $query->addField('t1', 'id', 'id');
    $query->addField('t1', 'sku', 'sku');
    $query->leftJoin('acq_sku_field_data', 't2', 't1.sku = t2.sku');
    $query->where('t1.id != t2.id');
    $result = $query->execute()->fetchAllKeyed(0, 1);

    if (empty($result)) {
      $this->output->writeln(dt('No duplicate skus found.'));
    }
    else {
      $skus = [];

      foreach ($result as $id => $sku) {
        $skus[$sku][$id] = $id;
        $skus_to_sync[$sku] = $sku;
      }

      foreach ($skus as $sku => $ids) {
        $this->output->writeln(dt('Duplicate skus found for sku: @sku with ids: @ids.', [
          '@sku' => $sku,
          '@ids' => implode(', ', $ids),
        ]));

        // Always delete the one with higher id, first one will have more
        // translations.
        sort($ids);

        // Remove the first id which we don't want to delete.
        array_shift($ids);

        foreach ($ids as $id) {
          $this->output->writeln(dt('Deleting sku with id @id for sku @sku.', [
            '@sku' => $sku,
            '@id' => $id,
          ]));

          $sku_entity = SKU::load($id);
          $sku_entity->delete();
        }
      }
    }

    $query = $this->connection->select('node__field_skus', 't1');
    $query->addField('t1', 'entity_id', 'id');
    $query->addField('t1', 'field_skus_value', 'sku');
    $query->leftJoin('node__field_skus', 't2', 't1.field_skus_value = t2.field_skus_value');
    $query->where('t1.entity_id != t2.entity_id');
    $result = $query->execute()->fetchAllKeyed(0, 1);

    if (empty($result)) {
      $this->output->writeln(dt('No duplicate product nodes found.'));
    }
    else {
      $nids_to_delete = [];
      $skus = [];

      foreach ($result as $id => $sku) {
        $skus[$sku][$id] = $id;
        $skus_to_sync[$sku] = $sku;
      }

      foreach ($skus as $sku => $ids) {
        $this->output->writeln(dt('Duplicate nodes found for sku: @sku with ids: @ids.', [
          '@sku' => $sku,
          '@ids' => implode(', ', $ids),
        ]));

        // Always delete the one with higher nid, first one will have proper
        // url alias.
        sort($ids);

        // Remove the first id which we don't want to delete.
        array_shift($ids);

        foreach ($ids as $id) {
          $this->output->writeln(dt('Deleting node with id @id for sku @sku.', [
            '@sku' => $sku,
            '@id' => $id,
          ]));

          $nids_to_delete[$id] = $id;
        }
      }

      if ($nids_to_delete) {
        $nodeStorage->delete($nodeStorage->loadMultiple($nids_to_delete));
      }
    }

    if ($skus_to_sync) {
      $sku_texts = implode(',', $skus_to_sync);

      $this->output->writeln(dt('Requesting resync for skus @skus.', [
        '@skus' => $sku_texts,
      ]));

      foreach ($this->i18nhelper->getStoreLanguageMapping() as $langcode => $store_id) {
        // Using very small page size to avoid any issues for skus which already
        // had corrupt data.
        $this->ingestApiWrapper->productFullSync($store_id, $langcode, $sku_texts, NULL, 5);
      }
    }

    $this->output->writeln(dt('Done.'));
  }

  /**
   * Flush all commerce data from the site (Products, SKUs, Product Categories and Product Options).
   *
   * @throws \Drush\Exceptions\UserAbortException
   *
   * @command acq_sku:flush-synced-data
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases accd,clean-synced-data
   *
   * @usage drush accd
   *   Flush all commerce data from the site (Products, SKUs, Product Categories and Product Options).
   */
  public function flushSyncedData() {
    if (!$this->io()->confirm(dt("Are you sure you want to clean commerce data?"))) {
      throw new UserAbortException();
    }
    $this->output->writeln(dt('Cleaning synced commerce data, please wait...'));

    // Set batch operation.
    $batch = [
      'title' => t('Clean synced data'),
      'init_message' => t('Cleaning synced commerce data starting...'),
      'operations' => [
        ['\Drupal\acq_sku\Commands\AcqSkuDrushCommands::skuCleanProcess', []],
      ],
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('Synced data could not be cleaned because an error occurred.'),
      'finished' => '_acq_sku_clean_finished',
    ];

    batch_set($batch);
    drush_backend_batch_process();
    $this->output->writeln(dt('Synced commerce data cleaned.'));
  }

  /**
   * Sync stock into all SKU entities using API.
   *
   * @command acq_sku:sync-stock
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases sync-stock
   *
   * @usage drush sync-stock
   *   Sync stock into all SKU entities using API.
   */
  public function syncStock() {
    $query = $this->connection->select('acq_sku_field_data', 'asfd');
    $query->addField('asfd', 'sku', 'sku');
    $query->condition('asfd.type', 'simple');
    $query->isNull('asfd.stock');
    $result = $query->execute()->fetchAllKeyed(0, 0);

    $this->output->writeln(sprintf('Found %d skus without stock info.', count($result)));

    if (empty($result)) {
      return;
    }

    $this->output->writeln('Processing in batches of 25');

    $batches = array_chunk($result, 25);

    // Entity storage can blow up with caches so clear them out.
    // We always process for en.
    $langcode = $this->languageManager->getDefaultLanguage()->getId();

    $counter = 0;

    foreach ($batches as $batch) {
      foreach ($batch as $sku) {
        $sku_entity = SKU::loadFromSku($sku, $langcode, FALSE, FALSE);

        // Sanity check, another process can delete the sku.
        if (empty($sku_entity)) {
          continue;
        }

        // Check again this sku doesn't have stock saved once.
        // Another process might have updated the info while we were processing.
        $stock = $sku_entity->get('stock')->getString();
        if (!($stock === '' || $stock === NULL)) {
          continue;
        }

        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface $plugin */
        $plugin = $sku_entity->getPluginInstance();
        $plugin->getProcessedStock($sku_entity, TRUE);

        // Reset static caches to release memory.
        drupal_static_reset();

        // Adding sleep of 200 ms to ensure API calls don't overload the server.
        usleep(200);
      }

      $counter++;

      $this->output->writeln(sprintf('Processed batch %d of %d.', $counter, count($batches)));

      foreach ($this->entityManager->getDefinitions() as $id => $definition) {
        $this->entityManager->getStorage($id)->resetCache();
      }
    }

    $this->output->writeln('Done');
  }

  /**
   * Function to process entity delete operation.
   *
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function skuCleanProcess(&$context) {
    // Use the $context['sandbox'] at your convenience to store the
    // information needed to track progression between successive calls.
    if (empty($context['sandbox'])) {
      // Get all the entities that need to be deleted.
      $context['sandbox']['results'] = [];

      // Get all acq_product entities.
      $query = \Drupal::entityQuery('node');
      $query->condition('type', 'acq_product');
      $product_entities = $query->execute();
      foreach ($product_entities as $entity_id) {
        $context['sandbox']['results'][] = [
          'type' => 'node',
          'entity_id' => $entity_id,
        ];
      }

      // Get all acq_sku entities.
      $query = \Drupal::entityQuery('acq_sku');
      $sku_entities = $query->execute();
      foreach ($sku_entities as $entity_id) {
        $context['sandbox']['results'][] = [
          'type' => 'acq_sku',
          'entity_id' => $entity_id,
        ];
      }

      // Get all taxonomy_term entities.
      $categories = ['acq_product_category', 'sku_product_option'];
      $query = \Drupal::entityQuery('taxonomy_term');
      $query->condition('vid', $categories, 'IN');
      $cat_entities = $query->execute();
      foreach ($cat_entities as $entity_id) {
        $context['sandbox']['results'][] = [
          'type' => 'taxonomy_term',
          'entity_id' => $entity_id,
        ];
      }

      // Allow other modules to add data to be deleted when cleaning up.
      \Drupal::moduleHandler()->alter('acq_sku_clean_synced_data', $context);

      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max'] = count($context['sandbox']['results']);
    }

    $results = [];
    if (isset($context['sandbox']['results']) && !empty($context['sandbox']['results'])) {
      $results = $context['sandbox']['results'];
    }

    $results = array_slice($results, isset($context['sandbox']['current']) ? $context['sandbox']['current'] : 0, self::DELETE_BATCH_COUNT);

    $delete = [];

    foreach ($results as $key => $result) {
      $context['results'][] = $results['type'] . ' : ' . $result['entity_id'];
      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $result['entity_id'];

      $delete[$result['type']][] = $result['entity_id'];

      // Update our progress information.
      $context['sandbox']['current']++;
    }

    foreach ($delete as $type => $entity_ids) {
      try {
        $storage = \Drupal::entityTypeManager()->getStorage($type);
        $entities = $storage->loadMultiple($entity_ids);
        $storage->delete($entities);
      }
      catch (\Exception $e) {
        \Drupal::logger('acq_sku')->error($e->getMessage());
      }
    }

    $context['message'] = 'Processed ' . $context['sandbox']['progress'] . ' out of ' . $context['sandbox']['max'] . '.';

    if ($context['sandbox']['progress'] !== $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Get stock cache for particular SKU.
   *
   * @command acq_sku:get-stock-cache
   *
   * @param string $sku
   *   SKU to get stock of.
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases acgsc,get-stock-cache
   *
   * @usage drush acgsc SKU
   *   Get stock cache for particular SKU.
   */
  public function getStockCache($sku) {
    if ($sku_entity = SKU::loadFromSku($sku)) {
      /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface $plugin */
      $plugin = $sku_entity->getPluginInstance();
      $this->output->writeln($plugin->getProcessedStock($sku_entity));
    }
    else {
      $this->output->writeln(dt('SKU not found.'));
    }
  }

  /**
   * Flush the stock cache.
   *
   * @return void
   *
   * @throws \Drush\Exceptions\UserAbortException
   *
   * @command acq_sku:flush-stock-cache
   *
   * @param array $options
   *
   * @option sku SKU to clean stock of.
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases accsc,clean-stock-cache
   *
   * @usage drush accsc
   *   Flush the stock cache for all SKUs.
   * @usage drush acsp --sku=SKU
   *   SKU to clean stock for particular SKU.
   */
  public function flushStockCache($options = ['sku' => NULL]) {
    // Check if we are asked to clear cache of specific SKU.
    if (!empty($options['sku'])) {
      if ($sku_entity = SKU::loadFromSku($options['sku'])) {
        $sku_entity->clearStockCache();

        $this->output->writeln(dt('Invalidated stock cache for @sku.', [
          '@sku' => $options['sku'],
        ]));
      }

      return;
    }

    if (!$this->io()->confirm(dt('Are you sure you want to clean stock cache?'))) {
      throw new UserAbortException();
    }

    $this->stockCache->deleteAllPermanent();

    $this->output->writeln(dt('Deleted all cache for stock.'));
  }

  /**
   * Clear linked SKUs cache.
   *
   * @return void
   *
   * @throws \Drush\Exceptions\UserAbortException
   *
   * @command acq_sku:clear-linked-skus-cache
   *
   * @param array $options
   *
   * @option sku SKU to clean linked skus cache of.
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases acclsc,clear-linked-skus-cache
   *
   * @usage drush acclsc
   *   Clear linked SKUs cache for all SKUs.
   * @usage drush acclsc --skus=SKU
   *   Clear linked SKUs cache for particular SKU.
   */
  public function flushLinkedSkuCache($options = ['sku' => NULL]) {
    // Check if we are asked to clear cache of specific SKU.
    if (!empty($options['sku'])) {
      if ($sku_entity = SKU::loadFromSku($options['sku'])) {
        $this->cacheTagsInvalidator->invalidateTags([
          'acq_sku:linked_skus:' . $sku_entity->id(),
          'acq_sku:' . $sku_entity->id(),
        ]);

        $this->output->writeln(dt('Invalidated linked SKUs cache for @sku.', [
          '@sku' => $options['sku'],
        ]));
      }

      return;
    }

    if (!$this->io()->confirm(dt('Are you sure you want to clear linked SKUs cache for all SKUs?'))) {
      throw new UserAbortException();
    }

    $this->linkedSkuCache->deleteAll();

    $this->output->writeln(dt('Cleared all linked SKUs cache.'));
  }

}
