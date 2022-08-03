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
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\FileInterface;
use Drupal\acq_sku\ConductorCategorySyncHelper;
use Drupal\taxonomy\TermInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Drupal\acq_sku\Event\ProcessBlackListedProductsEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Acq Sku Drush Commands.
 */
class AcqSkuDrushCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  public const DELETE_BATCH_COUNT = 200;

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
   * Category sync helper.
   *
   * @var \Drupal\acq_sku\ConductorCategorySyncHelper
   */
  private $categorySyncHelper;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $langaugeManager
   *   Language Manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $linkedSkuCache
   *   Cache Backend Service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   Cache Tags invalidator.
   * @param \Drupal\acq_sku\ConductorCategorySyncHelper $category_sync_helper
   *   Category sync helper.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   */
  public function __construct(APIWrapperInterface $apiWrapper,
                              I18nHelper $i18nHelper,
                              IngestAPIWrapper $ingestAPIWrapper,
                              ConductorCategoryManager $conductorCategoryManager,
                              ProductOptionsManager $productOptionsManager,
                              LoggerChannelFactoryInterface $loggerChannelFactory,
                              Connection $connection,
                              EntityTypeManagerInterface $entityTypeManager,
                              LanguageManagerInterface $langaugeManager,
                              ModuleHandlerInterface $moduleHandler,
                              CacheBackendInterface $linkedSkuCache,
                              CacheTagsInvalidatorInterface $cacheTagsInvalidator,
                              ConductorCategorySyncHelper $category_sync_helper,
                              EventDispatcherInterface $dispatcher) {
    parent::__construct();
    $this->apiWrapper = $apiWrapper;
    $this->i18nhelper = $i18nHelper;
    $this->ingestApiWrapper = $ingestAPIWrapper;
    $this->conductorCategoryManager = $conductorCategoryManager;
    $this->productOptionsManager = $productOptionsManager;
    $this->drupalLogger = $loggerChannelFactory->get('acq_sku');
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $langaugeManager;
    $this->moduleHandler = $moduleHandler;
    $this->linkedSkuCache = $linkedSkuCache;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->categorySyncHelper = $category_sync_helper;
    $this->dispatcher = $dispatcher;
  }

  /**
   * Run a full synchronization of all commerce product records.
   *
   * @param string $langcode
   *   Sync products available in this langcode.
   * @param string $page_size
   *   Number of items to be synced in one batch.
   * @param array $options
   *   Options.
   *
   * @option skus
   *   SKUs to import (like query).
   * @option category_id
   *   Magento category id to sync the products for.
   *
   * @command acq_sku:sync-products
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases acsp,sync-commerce-products
   *
   * @usage drush acsp en 50
   *   Run a full product synchronization of all available products in store
   *   linked to en and page size 50.
   * @usage drush acsp en 50 --skus=\'SKU 1',\'SKU 2',\'SKU 3\''
   *   Synchronize sku data for the skus SKU 1, SKU 2 & SKU 3 only in store
   *   linked to en and page size 50.
   * @usage drush acsp en 50 --category_id=1234
   *   Synchronize sku data for the skus in category with id 1234 only in store
   *   linked to en and page size 50.
   *
   * @throws \Drush\Exceptions\UserAbortException
   */
  public function syncProducts($langcode, $page_size, array $options = [
    'skus' => NULL,
    'category_id' => NULL,
  ]) {
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

    // Conditionally increase memory limit to double the current limit.
    $new_limit = (((int) ini_get('memory_limit')) * 2) . 'M';

    // We still let that be overridden via settings.
    $new_limit = Settings::get('acq_sku_sync_commerce_cats_memory_limit', $new_limit);

    ini_set('memory_limit', $new_limit);
    $this->drupalLogger->notice('Memory limit increased for sync-commerce-cats to @limit', [
      '@limit' => ini_get('memory_limit'),
    ]);

    $response = $this->conductorCategoryManager->synchronizeTree('acq_product_category');

    // We trigger delete only if there is any term update/create.
    // So if API does not return anything, we don't delete all the categories.
    if (!empty($response['created']) || !empty($response['updated'])) {
      // Get all category terms with commerce id.
      $orphan_categories = $this->conductorCategoryManager->getOrphanCategories($response);

      // If there are categories to delete.
      if (!empty($orphan_categories)) {
        // Show `tid + cat name + commerce id` for review.
        $this->io()->table(
          [
            dt('Category Id'),
            dt('Category Name'),
            dt('Category Commerce Id'),
          ],
          $orphan_categories
        );

        // Confirmation to delete old categories.
        if ($this->io()->confirm(dt('Are you sure you want to clean these old categories'), FALSE)) {
          $orphan_categories = array_keys($orphan_categories);

          // Allow other modules to skipping the deleting of terms.
          $this->moduleHandler->alter('acq_sku_sync_categories_delete', $orphan_categories);

          foreach ($orphan_categories as $tid) {
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
      $this->drupalLogger->notice(dt('Not cleaning(deleting) old terms as there is no term update/create.'));
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
    $this->drupalLogger->notice(dt('Synchronizing all commerce product options, please wait...'));
    $this->productOptionsManager->synchronizeProductOptions();
    $this->drupalLogger->notice(dt('Product attribute sync completed.'));
  }

  /**
   * Run a partial synchronization of commerce product records synchronously.
   *
   * @param int $count
   *   Number of product records to sync.
   *
   * @command acq_sku:sync-products-test
   *
   * @validate-module-enabled acq_sku
   *
   * @aliases acdsp,sync-commerce-products-test
   *
   * @usage drush acdsp
   *   Run a partial synchronization of commerce product records synchronously.
   */
  public function syncProductsTest($count) {
    $this->output->writeln(dt('Synchronizing @count commerce products for testing / dev...', ['@count' => $count]));

    // phpcs:ignore
    $container = \Drupal::getContainer();
    foreach ($this->i18nhelper->getStoreLanguageMapping() as $store_id) {
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
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

    /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
    $nodeStorage = $this->entityTypeManager->getStorage('node');

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
          if ((is_countable($tids) ? count($tids) : 0) == 1) {
            break;
          }
        }
        else {
          $this->output->writeln(dt('@count nodes found for tid: @tid for commerce id: @commerce_id. Not Deleting', [
            '@commerce_id' => $commerce_id,
            '@tid' => $tid,
            '@count' => is_countable($nodes) ? count($nodes) : 0,
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
   * Flush all commerce data from the site.
   *
   * For instance: Products, SKUs, Product Categories and Product Options.
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
   *   Flush all commerce data from the site (Products, SKUs, Product Categories
   *   and Product Options).
   */
  public function flushSyncedData() {
    if (!$this->io()->confirm(dt("Are you sure you want to clean commerce data?"))) {
      throw new UserAbortException();
    }
    $this->output->writeln(dt('Cleaning synced commerce data, please wait...'));

    // Set batch operation.
    $batch = [
      'title' => $this->t('Clean synced data'),
      'init_message' => $this->t('Cleaning synced commerce data starting...'),
      'operations' => [
        ['\Drupal\acq_sku\Commands\AcqSkuDrushCommands::skuCleanProcess', []],
      ],
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('Synced data could not be cleaned because an error occurred.'),
      'finished' => '_acq_sku_clean_finished',
    ];

    batch_set($batch);
    drush_backend_batch_process();
    $this->output->writeln(dt('Synced commerce data cleaned.'));
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

      $context_results = &$context['sandbox']['results'];
      // Allow other modules to add data to be deleted when cleaning up.
      \Drupal::moduleHandler()->alter('acq_sku_clean_synced_data', $context_results);
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max'] = is_countable($context['sandbox']['results']) ? count($context['sandbox']['results']) : 0;
    }

    $results = [];
    if (isset($context['sandbox']['results']) && !empty($context['sandbox']['results'])) {
      $results = $context['sandbox']['results'];
    }

    $results = array_slice($results, $context['sandbox']['current'] ?? 0, self::DELETE_BATCH_COUNT);

    $delete = [];

    foreach ($results as $result) {
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
   * Clear linked SKUs cache.
   *
   * @param array $options
   *   Options.
   *
   * @command acq_sku:clear-linked-skus-cache
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
   *
   * @throws \Drush\Exceptions\UserAbortException
   */
  public function flushLinkedSkuCache(array $options = ['sku' => NULL]) {
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

  /**
   * Command to go through all the media and find ones with corrupt data.
   *
   * It also marks them for re-downloading.
   *
   * @param string $field
   *   Field to check.
   * @param array $options
   *   Command options.
   *
   * @command acq_sku:fix-corrupt-sku-media
   *
   * @option batch_size
   *   Batch size.
   * @option skus
   *   Comma separated list of skus to limit process to those skus.
   * @options check_file_exists
   *   Check if file exists, this will take more time.
   * @options dry-run
   *   Do not update SKU but only show corrupt skus in logs.
   *
   * @usage drush fix-corrupt-sku-media media
   *   Process all the skus in system with fid in media data.
   * @usage drush fix-corrupt-sku-media media --skus="sku1,sku2"
   *   Process all the skus specified in option --sku (separated by comma).
   *
   * @aliases fix-corrupt-sku-media
   */
  public function fixCorruptSkuMedia($field = 'media', array $options = [
    'batch_size' => 50,
    'skus' => '',
    'check_file_exists' => FALSE,
    'dry-run' => FALSE,
  ]) {

    $batch_size = (int) $options['batch_size'];
    $check_file_exists = (bool) $options['check_file_exists'];
    $dry_run = (bool) $options['dry-run'];
    $skus = (string) $options['skus'];
    $skus = array_filter(explode(',', $skus));
    $verbose = $options['verbose'];

    $this->drupalLogger->notice('Checking all media...');

    $select = $this->connection->select('acq_sku_field_data');
    $select->fields('acq_sku_field_data', ['sku']);
    $select->condition('default_langcode', 1);

    if ($skus) {
      $select->condition('sku', $skus, 'IN');
    }
    else {
      $select->condition($field . '__value', '%fid%', 'LIKE');
    }

    $result = $select->execute()->fetchAll();

    $skus = array_column($result, 'sku');

    // If no sku available, then no need to process further as with empty
    // array, drush throws error.
    if (!$skus) {
      $this->output->writeln(dt('No matched sku found for corrupt media check.'));
      return;
    }

    $batch = [
      'title' => 'Process skus',
      'error_message' => 'Error occurred while processing skus, please check logs.',
    ];

    foreach (array_chunk($skus, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [self::class, 'correctCorruptMediaChunk'],
        [$chunk, $field, $check_file_exists, $dry_run, $verbose],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();

    $this->drupalLogger->notice('Processed all skus to find missing media items.');
  }

  /**
   * Batch callback.
   *
   * @param array $skus
   *   SKUs to process.
   * @param string $field
   *   Field name.
   * @param bool $check_file_exists
   *   Flag - check if file exists in file system or not.
   * @param bool $dry_run
   *   Flag - do not save skus yet, only output errors.
   * @param bool $verbose
   *   Flag - show debug output or not.
   */
  public static function correctCorruptMediaChunk(array $skus, string $field, $check_file_exists, $dry_run, $verbose) {
    $logger = \Drupal::logger('AcqSkuDrushCommands');

    $fileStorage = \Drupal::entityTypeManager()->getStorage('file');

    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');

    foreach ($skus as $sku_string) {
      $sku = SKU::loadFromSku($sku_string);
      if (!($sku instanceof SKU)) {
        continue;
      }

      // @codingStandardsIgnoreLine
      $media = unserialize($sku->get($field)->getString());

      $resave = FALSE;
      foreach ($media ?? [] as $index => $item) {
        // If fid is not set, we will let it be downloaded in
        // normal flow.
        if (empty($item['fid'])) {
          continue;
        }

        $redownload = '';
        // If fid is empty, we have some issue, we will redownload.
        if (empty($item['fid'])) {
          $redownload = 'missing fid';
        }
        else {

          $file = $fileStorage->load($item['fid']);

          if ($file instanceof FileInterface) {
            if ($check_file_exists) {
              $data = @file_get_contents($file_system->realpath($file->getFileUri()));
              if (empty($data)) {
                $redownload = 'missing file';
              }
            }
          }
          else {
            $redownload = 'missing file entity';
          }
        }

        if ($redownload) {
          $logger->error('Removing fid from media item from @sku, for @reason. @item.', [
            '@sku' => $sku->getSku(),
            '@reason' => $redownload,
            '@item' => $verbose ? json_encode($item, JSON_THROW_ON_ERROR) : '',
          ]);

          $resave = TRUE;

          unset($item['fid']);
          $media[$index] = $item;
        }
      }

      if ($resave && !$dry_run) {
        $sku->get($field)->setValue(serialize($media));
        $sku->save();
      }
    }
  }

  /**
   * Command to process category sync data after push from magento.
   *
   * @command acq_sku:cat-sync-process
   *
   * @usage drush cat-sync-process
   *   Process categories after push from magento.
   *
   * @aliases cat-sync-process
   */
  public function catSyncProcess() {
    $this->drupalLogger->notice(dt('Processing category sync for push mode. Please wait ...'));
    $this->categorySyncHelper->processCatSync();
    $this->drupalLogger->notice(dt('Processing category sync completed.'));
  }

  /**
   * Drush command that displays the given text.
   *
   * @validate-module-enabled acq_sku
   *
   * @command acq_sku:process-blacklisted-products
   *
   * @aliases acpbp,process-commerce-blacklisted-products
   *
   * @usage drush acpbp
   *   Run a full category synchronization of all available categories.
   */
  public function processBlacklistedProduct() {
    $event = new ProcessBlackListedProductsEvent();
    $this->dispatcher->dispatch(ProcessBlackListedProductsEvent::EVENT_NAME, $event);
  }

  /**
   * Drush command to get the item count we need to delete.
   *
   * Get the items count we need to delete during commerce data cleanup.
   *
   * @validate-module-enabled acq_sku
   *
   * @command acq_sku:get-item-delete-count
   *
   * @usage drush acq_sku:get-item-delete-count
   *   Get the items count we need to delete during commerce data cleanup.
   */
  public function getItemCountTodelete() {
    $result = $this->connection->query("select nid as entity_id, 'node' as type from node where type in ('acq_product', 'acq_promotion', 'store') union select id as entity_id, 'acq_sku' as type from acq_sku union select tid as entity_id, 'taxonomy_term' as type from taxonomy_term_data where vid='acq_product_category'");
    $data = $result->fetchAll(\PDO::FETCH_ASSOC);
    $this->moduleHandler->alter('acq_sku_clean_synced_data', $data);
    print is_countable($data) ? count($data) : 0;
  }

}
