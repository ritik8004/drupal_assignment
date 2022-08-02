<?php

namespace Drupal\alshaya_api\Commands;

use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\StockManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryMappingManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Class Alshaya Api Commands.
 *
 * @package Drupal\alshaya_api\Commands
 */
class AlshayaApiCommands extends DrushCommands {

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Alshaya Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  private $alshayaApiWrapper;

  /**
   * Sku manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * I18n Helper service.
   *
   * @var \Drupal\acq_commerce\InHelper
   */
  private $i18nHelper;

  /**
   * Conductor Api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\IngestAPIWrapper
   */
  private $ingestApiWrapper;

  /**
   * Database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Database lock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  private $lock;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Loaded Magento Data.
   *
   * @var array|null
   */
  protected $magentoData;

  /**
   * Loaded Drupal Data.
   *
   * @var array|null
   */
  protected $drupalData;

  /**
   * Stock Manager.
   *
   * @var \Drupal\acq_sku\StockManager
   */
  protected $stockManager;

  /**
   * Product Category Mapping Manager.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryMappingManager
   */
  protected $categoryMappingManager;

  /**
   * Drupal Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * AlshayaApiCommands constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshayaApiWrapper
   *   Alshaya Api Wrapper.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager service.
   * @param \Drupal\acq_commerce\I18nHelper $i18nHelper
   *   i18n Helper service.
   * @param \Drupal\acq_commerce\Conductor\IngestAPIWrapper $ingestAPIWrapper
   *   Conductor API wrapper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   Database lock service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\acq_sku\StockManager $stock_manager
   *   Stock Manager.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryMappingManager $category_mapping_manager
   *   Product Category Mapping Manager.
   */
  public function __construct(LanguageManagerInterface $languageManager,
                              AlshayaApiWrapper $alshayaApiWrapper,
                              SkuManager $skuManager,
                              I18nHelper $i18nHelper,
                              IngestAPIWrapper $ingestAPIWrapper,
                              LoggerChannelFactoryInterface $loggerChannelFactory,
                              Connection $connection,
                              EntityTypeManagerInterface $entityTypeManager,
                              LockBackendInterface $lock,
                              ConfigFactoryInterface $config_factory,
                              StockManager $stock_manager,
                              ProductCategoryMappingManager $category_mapping_manager) {
    $this->languageManager = $languageManager;
    $this->alshayaApiWrapper = $alshayaApiWrapper;
    $this->skuManager = $skuManager;
    $this->i18nHelper = $i18nHelper;
    $this->ingestApiWrapper = $ingestAPIWrapper;
    $this->drupalLogger = $loggerChannelFactory->get('AlshayaApiCommands');
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->lock = $lock;
    $this->configFactory = $config_factory;
    $this->stockManager = $stock_manager;
    $this->categoryMappingManager = $category_mapping_manager;

    parent::__construct();
  }

  /**
   * Log message.
   *
   * @param string $message
   *   Message to log.
   * @param array $arguments
   *   Log arguments.
   * @param bool $verbose
   *   Show in console or not.
   */
  protected function logMessage(string $message, array $arguments, $verbose = TRUE) {
    if ($verbose) {
      $this->drupalLogger->notice($message, $arguments);
    }
    else {
      $this->drupalLogger->info($message, $arguments);
    }
  }

  /**
   * Request product sync.
   *
   * Sync only once per request.
   *
   * @param array $skus
   *   SKUs to sync.
   * @param string|int $store_id
   *   Store id.
   * @param string $langcode
   *   Language code.
   * @param string|int $page_size
   *   Page size.
   */
  protected function requestSync(array $skus, $store_id, $langcode, $page_size) {
    static $already_requested = [];

    // Initialise.
    $already_requested[$langcode] ??= [];

    $skus = array_diff($skus, $already_requested[$langcode]);
    $already_requested[$langcode] = array_merge($already_requested[$langcode], $skus);

    $this->drupalLogger->notice('Requesting sync for skus: @skus (count: @count) in language: @langcode', [
      '@skus' => implode(',', $skus),
      '@count' => count($skus),
      '@langcode' => $langcode,
    ]);

    foreach (array_chunk($skus, $page_size) as $chunk) {
      $this->ingestApiWrapper->productFullSync(
        $store_id,
        $langcode,
        implode(',', $chunk),
        NULL,
        $page_size
      );
    }
  }

  /**
   * Get data from Magento.
   *
   * @return array
   *   Data from Magento.
   */
  protected function getDataFromMagento() {
    if (empty($this->magentoData)) {
      // Retrieve all enabled SKUs from Magento.
      $this->output()->writeln('Getting SKUs from Magento, please wait...');
      $this->magentoData = $this->alshayaApiWrapper->getSkusData();
    }

    return $this->magentoData ?? [];
  }

  /**
   * Drupal data.
   *
   * @return array
   *   Drupal data.
   */
  protected function getDataFromDrupal() {
    if (empty($this->drupalData)) {
      // Retrieve all data from Magento.
      $this->output()->writeln('Getting SKUs from Drupal, please wait...');

      $query = $this->connection->select('acq_sku_field_data', 'asfd');
      $query->join('acq_sku_stock', 'stock', 'stock.sku = asfd.sku');
      $query->fields('asfd', ['sku', 'price', 'final_price']);
      $query->fields('stock', ['quantity', 'status', 'max_sale_qty']);
      $query->condition('default_langcode', 1);
      $this->drupalData = $query->execute()->fetchAllAssoc('sku', \PDO::FETCH_ASSOC);
    }

    return $this->drupalData ?? [];
  }

  /**
   * Run sanity check to get diff between Magento and Drupal SKUs data.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check
   *
   * @option types
   *   The comma-separated list of SKUs types to check (simple, configurable).
   *
   * @aliases alshaya-api-sanity-check
   * @usage alshaya-api-sanity-check --check="category,price,stock,status" --page_size=3
   * @usage alshaya-api-sanity-check --check="category,stock"
   * @usage alshaya-api-sanity-check --check="category,status"
   * @usage alshaya-api-sanity-check --mode="sync"
   * @usage alshaya-api-sanity-check --mode="update"
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function sanityCheck(array $options = [
    'page_size' => 3,
    'check' => 'category,price,stock,status',
    'mode' => 'sync',
  ]) {
    $to_check = explode(',', $options['check']);

    // Increase memory limit for sanity check as we have a lot of skus
    // now on production.
    ini_set('memory_limit', '1024M');

    if (in_array('stock', $to_check)) {
      $this->sanityCheckStock($options);
    }

    if (in_array('category', $to_check)) {
      $this->sanityCheckCategoryMappings($options);
    }

    if (in_array('price', $to_check)) {
      $this->sanityCheckPrice($options);
    }

    if (in_array('status', $to_check)) {
      $this->sanityCheckStatus($options);
    }

    $this->output()->writeln('Sanity check completed.');
  }

  /**
   * Run sanity check to get diff between Magento and Drupal category mappings.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check-category-mapping
   *
   * @aliases alshaya-api-sanity-check-cats
   * @usage drush alshaya-api-sanity-check-cats
   * @usage drush alshaya-api-sanity-check-cats --page_size=2
   */
  public function sanityCheckCategoryMappings(array $options = [
    'page_size' => 3,
    'mode' => 'sync',
  ]) {
    $verbose = $options['verbose'] ?? FALSE;
    $mode = $options['mode'] ?? 'sync';
    if ($mode === 'update') {
      $verbose = TRUE;
    }

    $to_sync = [];

    // MDC categories those needs to be ignored while checking for the category
    // diff between drupal and mdc.
    $skip_cats = [];
    if (!empty($cats_to_ignore = $this->configFactory->get('alshaya_api.settings')->get('ignored_mdc_cats_on_sanity_check'))) {
      $skip_cats = explode(',', $cats_to_ignore);
    }

    $mskus = $this->getDataFromMagento();

    $result = $this->connection->query('SELECT field_skus_value, field_commerce_id_value FROM node__field_skus
    LEFT JOIN node__field_category_original category
      ON node__field_skus.entity_id = category.entity_id
    LEFT JOIN taxonomy_term__field_commerce_id
      ON category.field_category_original_target_id = taxonomy_term__field_commerce_id.entity_id')->fetchAll();

    $dskus = [];
    foreach ($result as $row) {
      $dskus[$row->field_skus_value]['category_ids'][] = $row->field_commerce_id_value;
    }

    foreach ($mskus as $sku => $row) {
      $message = '';

      // For whatever reason, we might not have the product in Drupal.
      // Skip category mis match for it.
      if (empty($dskus[$sku])) {
        continue;
      }

      $mids = explode(',', $row['category_ids'] ?? '');
      $dids = $dskus[$sku]['category_ids'] ?? [];

      $mids = array_filter($mids, fn($a) => !empty($a) && !(in_array($a, $skip_cats)));

      $dids = array_filter($dids);

      // Skip if both are empty.
      if (empty($mids) && empty($dids)) {
        continue;
      }
      // Sync if either is empty.
      elseif (empty($mids) || empty($dids)) {
        $message = dt('SKU: @sku, Magento IDs: @magento, Drupal IDs: @drupal', [
          '@sku' => $sku,
          '@magento' => implode(',', $mids),
          '@drupal' => implode(',', $dids),
        ]);

        $to_sync[$sku] = $mids;
      }
      // Sync if either of them have additional values or difference.
      elseif (array_diff($mids, $dids) || array_diff($dids, $mids)) {
        $message = dt('SKU: @sku, Magento IDs: @magento, Drupal IDs: @drupal', [
          '@sku' => $sku,
          '@magento' => implode(',', $mids),
          '@drupal' => implode(',', $dids),
        ]);

        $to_sync[$sku] = $mids;
      }

      if ($message) {
        $this->logMessage($message, [], $verbose);
      }
    }

    if (empty($to_sync)) {
      $this->drupalLogger->notice('No category mapping diff found.');
      return;
    }

    $confirmation = dt('Do you want to update product category mappings directly? Count: @count', [
      '@count' => count($to_sync),
    ]);

    if ($mode === 'update' && $this->io()->confirm($confirmation)) {
      foreach ($to_sync as $sku => $mids) {
        $this->categoryMappingManager->mapCategoriesToProduct($sku, $mids);
      }

      return;
    }

    $confirmation = dt('Do you want to sync products with category mismatch? Count: @count', [
      '@count' => count($to_sync),
    ]);

    if ($this->io()->confirm($confirmation)) {
      $this->requestSync(
        array_keys($to_sync),
        $this->configFactory->get('acq_commerce.store')->get('store_id'),
        'en',
        $options['page_size']
      );
    }
  }

  /**
   * Run sanity check to get diff between Magento and Drupal stock.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check-stock
   *
   * @aliases alshaya-api-sanity-check-stock
   * @usage drush alshaya-api-sanity-check-stock
   * @usage drush alshaya-api-sanity-check-stock --mode=sync
   * @usage drush alshaya-api-sanity-check-stock --mode=update
   * @usage drush alshaya-api-sanity-check-stock --page_size=2
   */
  public function sanityCheckStock(array $options = [
    'page_size' => 3,
    'mode' => 'sync',
  ]) {
    $verbose = $options['verbose'] ?? FALSE;
    $mode = $options['mode'] ?? 'sync';
    if ($mode === 'update') {
      $verbose = TRUE;
    }

    $to_sync = [];
    $to_update = [];

    $dskus = $this->getDataFromDrupal();
    $mskus = $this->getDataFromMagento();

    $this->output()->writeln('Finding stock differences.');

    foreach ($dskus as $sku => $data) {
      $mdata = $mskus[$sku] ?? [];

      // We will check in status if not available now in Magento.
      if (empty($mdata)) {
        continue;
      }

      // Type cleanup.
      $mdata['qty'] = (int) $mdata['qty'];
      $data['quantity'] = (int) $data['quantity'];

      // If stock in Drupal does not match with stock in Magento.
      if ($mdata['type_id'] == 'simple' && $data['quantity'] !== $mdata['qty']) {
        $message = $sku . ' | ';
        $message .= 'Drupal stock:' . $data['quantity'] . ' | ';
        $message .= 'MDC stock:' . $mdata['qty'];
        $this->logMessage($message, [], $verbose);

        $to_update[$sku] = $mdata;

        $to_sync['stock_quantity'][] = $sku;
        continue;
      }

      // Check for stock status for simple products having stock and
      // configurable products.
      if (($data['quantity'] > 0 || $mdata['type_id'] == 'configurable')
        && $data['status'] != $mdata['stock_status']) {
        $message = $sku . ' | ';
        $message .= 'Drupal stock status:' . $data['status'] . ' | ';
        $message .= 'MDC stock status:' . $mdata['stock_status'];
        $this->logMessage($message, [], $verbose);

        $to_update[$sku] = $mdata;

        $to_sync['stock_status'][] = $sku;
        continue;
      }

      // Ignore check for max_sale_qty if we don't get value for
      // use_config_max_sale_qty from Sanity API.
      if (!isset($mdata['use_config_max_sale_qty'])) {
        continue;
      }

      // Set max_sale_qty to 0 if it is coming from config.
      $mdata['max_sale_qty'] = !empty($mdata['use_config_max_sale_qty'])
        ? 0
        : $mdata['max_sale_qty'];

      if ($data['max_sale_qty'] != (int) $mdata['max_sale_qty']) {
        $message = $sku . ' | ';
        $message .= 'Drupal max sale quantity:' . $data['max_sale_qty'] . ' | ';
        $message .= 'MDC max sale quantity:' . $mdata['max_sale_qty'];
        $this->logMessage($message, [], $verbose);

        $to_update[$sku] = $mdata;

        $to_sync['stock_max_sale_qty'][] = $sku;
        continue;
      }
    }

    if (empty($to_update)) {
      $this->drupalLogger->notice('No stock difference found.');
      return;
    }

    $confirmation = dt('Do you want to update stock status in database directly? Count: @count', [
      '@count' => count($to_update),
    ]);

    if ($mode === 'update' && $this->io()->confirm($confirmation)) {
      foreach ($to_update as $sku => $mdata) {
        $this->stockManager->updateStock(
          $sku,
          (float) $mdata['qty'],
          (int) $mdata['stock_status'],
          (float) $mdata['max_sale_qty'],
          (bool) $mdata['use_config_max_sale_qty']
        );

        $this->logMessage(
          'Updated stock for SKU: @sku, data: @data.',
          ['@sku' => $sku, '@data' => json_encode($mdata)],
          $verbose
        );
      }

      return;
    }

    // Sync?
    foreach ($to_sync as $type => $skus) {
      $confirmation = dt('Do you want to sync products with @type mismatch? Count: @count', [
        '@count' => count($skus),
        '@type' => $type,
      ]);

      if ($this->io()->confirm($confirmation)) {
        $this->requestSync(
          $skus,
          $this->configFactory->get('acq_commerce.store')->get('store_id'),
          'en',
          $options['page_size']
        );
      }
    }
  }

  /**
   * Run sanity check to get diff between Magento and Drupal price.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check-price
   *
   * @aliases alshaya-api-sanity-check-price
   * @usage drush alshaya-api-sanity-check-price
   * @usage drush alshaya-api-sanity-check-price --page_size=2
   */
  public function sanityCheckPrice(array $options = [
    'page_size' => 3,
    'mode' => 'sync',
  ]) {
    $verbose = $options['verbose'] ?? FALSE;
    $mode = $options['mode'] ?? 'sync';
    if ($mode === 'update') {
      $verbose = TRUE;
    }

    $to_sync = [];

    $dskus = $this->getDataFromDrupal();
    $mskus = $this->getDataFromMagento();

    $this->output()->writeln('Finding price differences.');

    foreach ($dskus as $sku => $data) {
      $mdata = $mskus[$sku] ?? [];

      // We will check in status if not available now in Magento.
      if (empty($mdata) || $mdata['type_id'] !== 'simple') {
        continue;
      }

      // Cast prices to ensure comparison is between apple to apple.
      $demical_point = $this->configFactory->get('acq_commerce.currency')->get('decimal_points');
      $d_price = (float) $data['price'];
      $d_final_price = (float) round($data['final_price'], $demical_point);
      $m_price = (float) $mdata['price'];
      $m_final_price = (float) round($mdata['final_price'], $demical_point);

      // If price in Drupal not matches with price in Magento.
      if (($d_price != $m_price) || ($d_final_price != $m_final_price)) {
        $message = $sku . ' | ';
        $message .= 'Drupal price:' . $d_price . ' | ';
        $message .= 'MDC price:' . $m_price . ' | ';
        $message .= 'Drupal final price:' . $d_final_price . ' | ';
        $message .= 'MDC final price:' . $m_final_price;
        $this->logMessage($message, [], $verbose);

        $to_sync[$sku] = $mdata;
      }
    }

    if (empty($to_sync)) {
      $this->drupalLogger->notice('No price difference found.');
      return;
    }

    // Sync?
    $confirmation = dt('Do you want to update product prices directly? Count: @count', [
      '@count' => count($to_sync),
    ]);

    if ($mode === 'update' && $this->io()->confirm($confirmation)) {
      foreach ($to_sync as $sku => $mdata) {
        $sku_entity = SKU::loadFromSku($sku);
        if ($sku_entity instanceof SKU) {
          $sku_entity->get('price')->setValue($mdata['price']);
          $sku_entity->get('final_price')->setValue($mdata['final_price']);
          $sku_entity->save();

          $this->logMessage('Updated price for SKU @sku.', ['@sku' => $sku], $verbose);
        }
      }

      return;
    }

    // Sync?
    $confirmation = dt('Do you want to sync products with price mismatch? Count: @count', [
      '@count' => count($to_sync),
    ]);

    if ($this->io()->confirm($confirmation)) {
      $this->requestSync(
        array_keys($to_sync),
        $this->configFactory->get('acq_commerce.store')->get('store_id'),
        'en',
        $options['page_size']
      );
    }
  }

  /**
   * Run sanity check to get diff between Magento and Drupal status.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check-status
   *
   * @aliases alshaya-api-sanity-check-status
   * @usage drush alshaya-api-sanity-check-status
   * @usage drush alshaya-api-sanity-check-status --page_size=2
   */
  public function sanityCheckStatus(array $options = ['page_size' => 3]) {
    $dskus = $this->getDataFromDrupal();
    $mskus = $this->getDataFromMagento();

    $this->output()->writeln('Finding status differences.');

    $in_drupal_only = array_diff(array_keys($dskus), array_keys($mskus));
    $in_magento_only = array_diff(array_keys($mskus), array_keys($dskus));

    if (!empty($in_magento_only)) {
      $this->logMessage('Products not available in Drupal: @skus.', [
        '@skus' => implode(', ', $in_magento_only),
      ]);

      $confirmation = dt('Do you want to sync products that are not available in Drupal? Count: @count', [
        '@count' => count($in_magento_only),
      ]);

      if ($this->io()->confirm($confirmation)) {
        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          $this->requestSync(
            $in_magento_only,
            $store_id,
            $langcode,
            $options['page_size']
          );
        }
      }
    }

    if (!empty($in_drupal_only)) {
      $this->logMessage('Products available only in Drupal: @skus.', [
        '@skus' => implode(', ', $in_drupal_only),
      ]);

      $confirmation_delete = dt('Do you want to delete products directly in Drupal? Count: @count', [
        '@count' => count($in_drupal_only),
      ]);

      $confirmation_sync = dt('Do you want to request products not available in Magento again to let them autocorrect? Count: @count', [
        '@count' => count($in_drupal_only),
      ]);

      $verbose = $options['verbose'] ?? FALSE;

      if ($this->io()->confirm($confirmation_delete)) {
        foreach ($in_drupal_only as $sku) {
          $sku_entity = SKU::loadFromSku($sku, NULL, FALSE, FALSE);
          if (!($sku_entity instanceof SKU)) {
            $this->output()->writeln('Not able to load SKU Entity for ' . $sku);
            continue;
          }

          $this->logMessage('Removing disabled SKU @sku from the system.',
            ['@sku' => $sku],
            $verbose
          );

          $lock_key = 'deleteProduct' . $sku;

          // Acquire lock to ensure parallel processes are executed
          // sequentially.
          // @todo These 8 lines might be duplicated in multiple places. We
          // may want to create a utility service in alshaya_performance.
          do {
            $lock_acquired = $this->lock->acquire($lock_key);

            // Sleep for half a second before trying again.
            // @todo Move this 0.5s to a config variable.
            if (!$lock_acquired) {
              usleep(500000);
            }
          } while (!$lock_acquired);

          // Delete the node if it is linked to this SKU only.
          try {
            if ($node = $sku_entity->getPluginInstance()->getDisplayNode($sku_entity, FALSE, FALSE)) {
              $node->delete();
            }
          }
          catch (\Exception) {
            // Not doing anything, we might not have node for the sku.
          }

          // Delete the SKU.
          $sku_entity->delete();

          // Release the lock.
          $this->lock->release($lock_key);

          $this->logMessage('Disabled SKU @sku removed from the system.',
            ['@sku' => $sku],
            $verbose
          );
        }
      }
      elseif ($this->io()->confirm($confirmation_sync)) {
        $this->requestSync(
          $in_drupal_only,
          $this->configFactory->get('acq_commerce.store')->get('store_id'),
          'en',
          $options['page_size']
        );
      }
    }
  }

  /**
   * Run sanity check to get a diff of SKUs between Drupal and Magento.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check-sku-diff
   *
   * @option types
   *   The comma-separated list of SKUs types to check (simple, configurable).
   * @option magento_source
   *   The source to get the SKUs (api, report). Default is merchandising
   *   report.
   * @option page_size
   *   ACM page size.
   * @option use_delete
   *   Hidden deletion option.
   * @option live_check
   *   In context of merchandising report as magento source, confirm the diff
   *   via api to avoid mismatch due to outdated report.
   * @option use_cached_report
   *
   * @aliases aascsd,alshaya-api-sanity-check-sku-diff
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function sanityCheckSkuDiff(
    array $options = [
      'types' => 'simple,configurable',
      'magento_source' => 'report',
      'page_size' => 10,
      'use_delete' => FALSE,
      'live_check' => FALSE,
      'use_cached_report' => FALSE,
    ]
  ) {
    $debug = $options['debug'];
    $verbose = $options['verbose'];

    $types = array_map('trim', explode(',', $options['types']));

    $msource = $options['magento_source'];
    $page_size = $options['page_size'];
    $use_delete = $options['use_delete'];
    $use_cached_report = $options['use_cached_report'];

    // We want to be sure the live_check is used only for report.
    $live_check = $msource == 'report' ? $options['live_check'] : FALSE;

    $languages = $this->languageManager->getLanguages();

    // Retrieve all enabled SKUs from Magento indexed by type.
    $this->output()->writeln(dt('Getting @types SKUs from Magento, please wait...', [
      '@types' => implode(dt(' and '), $types),
    ]));

    if ($msource == 'report') {
      $mskus = $this->alshayaApiWrapper->getEnabledSkusFromMerchandisingReport($types, !$use_cached_report);
    }
    else {
      $mskus = $this->alshayaApiWrapper->getSkus($types);
    }

    if ($debug) {
      foreach ($types as $type) {
        $this->output()->writeln(dt("@type SKUs (@count) from Magento:\n@skus", [
          '@type' => $type,
          '@count' => is_countable($mskus[$type]) ? count($mskus[$type]) : 0,
          '@skus' => "'" . implode("','", array_keys($mskus[$type])) . "'",
        ]));
      }

      // Notify in debug mode.
      if ($msource == 'api') {
        $this->drupalLogger->notice('With source=api, stock and price will not be validated.');
      }
    }

    // Retrieve all enabled SKUs from Drupal indexed by type and langcode.
    $this->output()->writeln(dt("\nGetting @types SKUs from Drupal, please wait...", [
      '@types' => implode(dt(' and '), $types),
    ]));

    foreach ($types as $type) {
      foreach ($languages as $language) {
        $dskus[$type][$language->getId()] = $this->skuManager->getSkus($language->getId(), $type);

        if ($debug) {
          $this->output()->writeln(dt("@type @language SKUs (@count) from Drupal:\n@skus", [
            '@type' => $type,
            '@language' => $language->getName(),
            '@count' => count($dskus[$type][$language->getId()]),
            '@skus' => "'" . implode("','", array_keys($dskus[$type][$language->getId()])) . "'",
          ]));
        }
      }
    }

    $this->output()->writeln(dt("\n#### SUMMARY ####"));

    $missing = [];
    $to_be_deleted = [];
    $stock_mismatch_sync = [];
    $price_mismatch_sync = [];

    foreach ($types as $type) {
      $missing[$type]['all'] = [];
      $to_be_deleted[$type]['all'] = [];
      $stock_mismatch[$type] = [];
      $price_mismatch[$type] = [];

      foreach ($languages as $language) {
        // The ones which are missing in Drupal.
        $missing[$type][$language->getId()] = array_diff(array_keys($mskus[$type]), array_keys($dskus[$type][$language->getId()]));

        // If live-check is enabled, we confirm the missing SKUs are enabled
        // in MDC.
        if ($live_check && !empty($missing[$type][$language->getId()])) {
          $mskus2 = $this->alshayaApiWrapper->getSkus([$type], $missing[$type][$language->getId()]);
          $missing[$type][$language->getId()] = array_diff(array_keys($mskus2[$type]), array_keys($dskus[$type][$language->getId()]));
        }
        $mall = array_merge($missing[$type]['all'], $missing[$type][$language->getId()]);
        $missing[$type]['all'] = $mall;

        // The ones which are only in Drupal and should be removed.
        $to_be_deleted[$type][$language->getId()] = array_diff(array_keys($dskus[$type][$language->getId()]), array_keys($mskus[$type]));

        // If live-check is enabled, we confirm none of the identified SKUs to
        // be deleted are actually enabled in MDC. If any, we remove these from
        // the list of SKUs to be deleted from Drupal.
        if ($live_check && !empty($to_be_deleted[$type][$language->getId()])) {
          $enabled = $this->alshayaApiWrapper->getSkus([$type], $to_be_deleted[$type][$language->getId()]);

          if (!empty($enabled[$type])) {
            $to_be_deleted[$type][$language->getId()] = array_diff($to_be_deleted[$type][$language->getId()], array_keys($enabled[$type]));
          }
        }
        $tall = array_merge($to_be_deleted[$type]['all'], $to_be_deleted[$type][$language->getId()]);
        $to_be_deleted[$type]['all'] = $tall;

        if (!empty($missing[$type][$language->getId()])) {
          $this->output()->writeln(dt("\n@count @language @type's SKUs are missing in Drupal and must be synced!skus", [
            '@count' => count($missing[$type][$language->getId()]),
            '@language' => $language->getName(),
            '@type' => $type,
            '!skus' => $verbose ? ":\n'" . implode("','", $missing[$type][$language->getId()]) . "'" : '.',
          ]));
        }
        else {
          $this->output()->writeln(dt("\nNo missing SKUs match for @language @type in Drupal.", [
            '@language' => $language->getName(),
            '@type' => $type,
          ]));
        }

        if (!empty($to_be_deleted[$type][$language->getId()])) {
          $this->output()->writeln(dt("\n@count @language @type's SKUs are only in Drupal and must be removed!skus", [
            '@count' => count($to_be_deleted[$type][$language->getId()]),
            '@language' => $language->getName(),
            '@type' => $type,
            '!skus' => $verbose ? ":\n'" . implode("','", $to_be_deleted[$type][$language->getId()]) . "'" : '.',
          ]));
        }
        else {
          $this->output()->writeln(dt("\nNo additional SKUs for @language @type found in Drupal. Nothing to delete.", [
            '@language' => $language->getName(),
            '@type' => $type,
          ]));
        }
      }

      // We check stock and price only in case of merch because the main API
      // from Magento does not provide the stock information. Also, the stock
      // and price fields are not translatable.
      if ($type == 'simple' && $msource == 'report') {
        $checked_skus = [];

        // Variable to hold total requests be made to MDC for live check.
        $live_check_total_api_request = 0;
        // Get total number of api requests that will be made against magento
        // on stock/price difference when `live_check' flag is used and inform
        // the user about it.
        if ($live_check) {
          $total_live_check_api_call_stock = [];
          $total_live_check_api_call_price = [];
          $mskus_data = $mskus[$type];
          foreach ($languages as $lang) {
            $dskus_data = $dskus[$type][$lang->getId()];

            // Check if stock diff.
            $total_live_check_api_call_stock[] = array_filter($dskus_data, fn($sku_data) => !empty($mskus_data[$sku_data['sku']])
            && $sku_data['quantity'] != $mskus_data[$sku_data['sku']]['qty']);

            // Check if price diff.
            $total_live_check_api_call_price[] = array_filter($dskus_data, fn($sku_data) => !empty($mskus_data[$sku_data['sku']])
            && (
              $sku_data['price'] != $mskus_data[$sku_data['sku']]['price']
              || $sku_data['special_price'] != $mskus_data[$sku_data['sku']]['special_price']
            ));
          }

          if (!empty($total_live_check_api_call_stock) || !empty($total_live_check_api_call_price)) {
            $live_check_total_api_request = count(array_merge(...$total_live_check_api_call_stock))
              + count(array_merge(...$total_live_check_api_call_price));
            $this->io()->note(dt('Total:@count API calls will be made to MDC for stock/price difference.', [
              '@count' => $live_check_total_api_request,
            ]));
          }
        }

        // Variable to hold incremental value after each live_check api call.
        $live_check_api_call_done = 0;
        // Show live_check api call message after these number of api calls.
        $live_check_api_call_step = 50;

        foreach ($languages as $language) {
          foreach ($dskus[$type][$language->getId()] as $sku => $data) {
            // We will check the stock and price for this SKU only if it exists
            // in Magento (otherwise it must be deleted) and if it has not been
            // checked yet in another language.
            if (!empty($mskus[$type][$sku]) && !in_array($sku, $checked_skus)) {
              $checked_skus[] = $sku;

              $stock_output = '';
              $price_output = '';

              // If stock in drupal does not match with stock from merch and
              // live-check is enabled, we get the stock from API to confirm
              // the stock difference.
              if ($live_check && $data['quantity'] != $mskus[$type][$sku]['qty']) {
                $live_check_api_call_done++;
                if (($live_check_api_call_done % $live_check_api_call_step) == 0) {
                  $this->output()->writeln(dt("@current/@total Fetching data from Magento's API for stock and price. Please wait ...", [
                    '@current' => $live_check_api_call_done,
                    '@total' => $live_check_total_api_request,
                  ]));
                }
                $mskus[$type][$sku]['qty'] = $this->alshayaApiWrapper->getStock($sku);
              }

              // If stock in drupal does not match with stock from merch.
              if ($data['quantity'] != (int) $mskus[$type][$sku]['qty']) {
                $stock_output .= 'Drupal stock:' . $data['quantity'] . ' | ';
                $stock_output .= 'MDC stock:' . (int) $mskus[$type][$sku]['qty'];
                $stock_mismatch_sync[$type][] = $sku;
              }

              // If any of the prices from Drupal and merch report diverge and
              // live-check is enabled, we get the prices from API to confirm
              // the price differences.
              if ($live_check && (
                  $data['price'] != $mskus[$type][$sku]['price'] || $data['special_price'] != $mskus[$type][$sku]['special_price'])
              ) {
                $live_check_api_call_done++;
                if (($live_check_api_call_done % $live_check_api_call_step) == 0) {
                  $this->output()->writeln(dt("@current/@total Fetching data from Magento's API for stock and price. Please wait ...", [
                    '@current' => $live_check_api_call_done,
                    '@total' => $live_check_total_api_request,
                  ]));
                }
                $sku_data = $this->alshayaApiWrapper->getSku($sku);

                if (isset($sku_data['price'])) {
                  $mskus[$type][$sku]['price'] = $sku_data['price'];
                }

                if (!empty($sku_data['custom_attributes'])) {
                  foreach ($sku_data['custom_attributes'] as $attribute) {
                    if ($attribute['attribute_code'] == 'special_price') {
                      $mskus[$type][$sku]['special_price'] = $attribute['value'];
                      break;
                    }
                  }
                }
              }

              // If price in drupal not matches with what in magento.
              if ($data['price'] != $mskus[$type][$sku]['price']) {
                $price_output .= 'Drupal price:' . $data['price'] . ' | ';
                $price_output .= 'MDC price:' . $mskus[$type][$sku]['price'];
                $price_mismatch_sync[$type][] = $sku;
              }

              // If special price in drupal not matches with what in magento.
              if ($data['special_price'] != $mskus[$type][$sku]['special_price']) {
                $price_output .= 'Drupal spl price:' . $data['special_price'] . ' | ';
                $price_output .= 'MDC spl price:' . $mskus[$type][$sku]['special_price'];
                $price_mismatch_sync[$type][] = $sku;
              }

              // If there any sku having stock mismatch.
              if (!empty($stock_output)) {
                $stock_mismatch[$type][$sku] = "SKU:" . $sku . " | " . $stock_output;
              }

              // If there any sku having price mismatch.
              if (!empty($price_output)) {
                $price_mismatch[$type][$sku] = "SKU:" . $sku . " | " . $price_output;
              }
            }
          }
        }

        // Output the details of stock mismatch.
        if (!empty($stock_mismatch[$type])) {
          $this->output()->writeln(dt("\n@count @type's SKUs in drupal have different stock than magento!output", [
            '@count' => count($stock_mismatch[$type]),
            '@type' => $type,
            '!output' => $verbose ? ":\n" . implode("\n", $stock_mismatch[$type]) : '.',
          ]));
        }
        else {
          $this->output()->writeln(dt("\nNo stock mismatch for @type's in Drupal.", [
            '@type' => $type,
          ]));
        }

        // Output the details of price mismatch.
        if (!empty($price_mismatch[$type])) {
          $this->output()->writeln(dt("\n@count @type's SKUs in drupal have different price than magento!output", [
            '@count' => count($price_mismatch[$type]),
            '@type' => $type,
            '!output' => $verbose ? ":\n" . implode("\n", $price_mismatch[$type]) : '.',
          ]));
        }
        else {
          $this->output()->writeln(dt("\nNo price mismatch for @type's in Drupal.", [
            '@type' => $type,
          ]));
        }
      }

      $missing[$type]['all'] = array_unique($missing[$type]['all']);
      $to_be_deleted[$type]['all'] = array_unique($to_be_deleted[$type]['all']);
      $stock_mismatch_sync[$type] = !empty($stock_mismatch_sync[$type]) ? array_unique($stock_mismatch_sync[$type]) : [];
      $price_mismatch_sync[$type] = !empty($price_mismatch_sync[$type]) ? array_unique($price_mismatch_sync[$type]) : [];
    }

    $this->output()->writeln(dt("\n#### SYNCHRONIZATION ####"));

    $chunk_size = 100;

    // Sync store/price mis-match skus only for the merch report. Because
    // stock and price are not translatable, we only launch the sync for one
    // language.
    if ($msource == 'report') {
      // Sync stock/price mis-match skus.
      foreach ($types as $type) {

        // Stock/Price mis-match sku sync only for simple skus.
        if ($type != 'simple') {
          continue;
        }

        $stores = $this->i18nHelper->getStoreLanguageMapping();
        $langcode = key($stores);
        $store_id = $stores[$langcode];

        // Stock mismatch synchronization.
        if (!empty($stock_mismatch_sync[$type]) && $this->io()->confirm(dt('Do you want to sync the @count @type stock mismatch SKUs?', [
          '@count' => count($stock_mismatch_sync[$type]),
          '@type' => $type,
        ]))) {
          // We split the list of SKUs in small chunk to avoid any issue. This
          // is only to send the request to Conductor.
          foreach (array_chunk(str_replace("'", '', $stock_mismatch_sync[$type]), $chunk_size) as $chunk) {
            // @todo Make page size a config. It can be used in multiple places.
            // @todo It seems there is nothing being logged when fullSync is
            // launched.
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output()->writeln(dt('Sync launched for the @count @type SKUs with stock mismatch.', [
            '@count' => count($stock_mismatch_sync[$type]),
            '@type' => $type,
          ]));
        }

        // Price mismatch synchronization.
        if (!empty($price_mismatch_sync[$type]) && $this->io()->confirm(dt('Do you want to sync the @count @type price mismatch SKUs?', [
          '@count' => count($price_mismatch_sync[$type]),
          '@type' => $type,
        ]))) {
          // We split the list of SKUs in small chunk to avoid any issue. This
          // is only to send the request to Conductor.
          foreach (array_chunk(str_replace("'", '', $price_mismatch_sync[$type]), $chunk_size) as $chunk) {
            // @todo Make page size a config. It can be used in multiple places.
            // @todo It seems there is nothing being logged when fullSync is
            // launched.
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output()->writeln(dt('Sync launched for the @count @type SKUs with price mismatch.', [
            '@count' => count($price_mismatch_sync[$type]),
            '@type' => $type,
          ]));
        }
      }
    }

    // Retrieve missing SKUs.
    foreach ($types as $type) {
      if (!empty($missing[$type]['all']) && $this->io()->confirm(dt('Do you want to sync the @count @type missing SKUs?', [
        '@count' => count($missing[$type]['all']),
        '@type' => $type,
      ]))) {
        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          // We split the list of SKUs in small chunk to avoid any issue. This
          // is only to send the request to Conductor.
          foreach (array_chunk(str_replace("'", '', $missing[$type]['all']), $chunk_size) as $chunk) {
            // @todo Make page size a config. It can be used in multiple places.
            // @todo It seems there is nothing being logged when fullSync is
            // launched.
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output()->writeln(dt('Sync launched for the @count @language @type SKUs.', [
            '@count' => count($missing[$type]['all']),
            '@language' => $languages[$langcode]->getName(),
            '@type' => $type,
          ]));
        }
      }
    }

    // Try to resync extra SKUs.
    foreach ($types as $type) {
      if (!empty($to_be_deleted[$type]['all']) && $this->io()->confirm(dt('Do you want to sync the @count @type extra SKUs?', [
        '@count' => count($to_be_deleted[$type]['all']),
        '@type' => $type,
      ]))) {
        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          // We split the list of SKUs in small chunk to avoid any issue. This
          // is only to send the request to Conductor.
          foreach (array_chunk(str_replace("'", '', $to_be_deleted[$type]['all']), $chunk_size) as $chunk) {
            // @todo Make page size a config. It can be used in multiple places.
            // @todo It seems there is nothing being logged when fullSync is
            // launched.
            $this->ingestApiWrapper->productFullSync($store_id, $langcode, implode(',', $chunk), NULL, $page_size);
          }

          $this->output()->writeln(dt('Sync launched for the @count @language @type SKUs.', [
            '@count' => count($to_be_deleted[$type]['all']),
            '@language' => $languages[$langcode]->getName(),
            '@type' => $type,
          ]));
        }
      }
    }

    if ($use_delete === FALSE) {
      return;
    }

    // Delete additional SKUs.
    // @todo Some of the code to delete node + sku is duplicate from
    // ProductSyncResource::post(). We might want a service to do that task and
    // remove code duplication.
    foreach ($types as $type) {
      if (!empty($to_be_deleted[$type]['all']) && $this->io()->confirm(dt('Do you want to delete the @count @type additional SKUs?', [
        '@count' => count($to_be_deleted[$type]['all']),
        '@type' => $type,
      ]))) {
        foreach ($languages as $langcode => $language) {
          foreach ($to_be_deleted[$type][$langcode] as $sku) {
            if ($sku_entity = SKU::loadFromSku($sku, $langcode, FALSE, TRUE)) {
              $this->drupalLogger->notice('Removing disabled @language @type SKU @sku from the system: @sku.', [
                '@language' => $languages[$langcode]->getName(),
                '@type' => $type,
                '@sku' => $sku,
              ]);

              $lock_key = 'deleteProduct' . $sku;

              // Acquire lock to ensure parallel processes are executed
              // sequentially.
              // @todo These 8 lines might be duplicated in multiple places. We
              // may want to create a utility service in alshaya_performance.
              do {
                $lock_acquired = $this->lock->acquire($lock_key);

                // Sleep for half a second before trying again.
                // @todo Move this 0.5s to a config variable.
                if (!$lock_acquired) {
                  usleep(500000);
                }
              } while (!$lock_acquired);

              // Delete the node if it is linked to this SKU only.
              try {
                if ($node = $sku_entity->getPluginInstance()->getDisplayNode($sku_entity, FALSE, FALSE)) {
                  $node->delete();
                }
              }
              catch (\Exception) {
                // Not doing anything, we might not have node for the sku.
              }

              // Delete the SKU.
              $sku_entity->delete();

              // Release the lock.
              $this->lock->release($lock_key);

              $this->output()->writeln(dt('Disabled @language @type SKU @sku removed from the system.', [
                '@language' => $languages[$langcode]->getName(),
                '@type' => $type,
                '@sku' => $sku,
              ]));
            }
          }
        }
      }
    }
  }

  /**
   * Sanity check to identify products.
   *
   * Run Sanity check to identify products from SKUs which are not supposed to
   * be visible.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @command alshaya_api:sanity-check-visibility
   *
   * @aliases aascv,alshaya-api-sanity-check-visibility
   * @options display_nids Boolean flag to display list of nodes.
   */
  public function sanityCheckVisibility(array $options = ['display_nids' => FALSE]) {
    $verbose = $options['display_nids'];

    $handle = $this->alshayaApiWrapper->getMerchandisingReport();

    if (!$handle) {
      $this->output()->writeln(dt('Impossible to get the merchandising report from Magento.'));
      return;
    }

    // Because the column position may vary across brands, we are browsing the
    // report's first line to identify the position of each column we need.
    $indexes = [
      'partnum' => FALSE,
      'visibility' => FALSE,
    ];

    $not_visible_ind_skus = [];

    if ($data = fgetcsv($handle, 1000, ',')) {
      foreach ($data as $position => $key) {
        foreach ($indexes as $name => $index) {
          if (trim(strtolower($key)) == $name) {
            $indexes[$name] = $position;
            continue;
          }
        }
      }

      if (in_array(FALSE, $indexes)) {
        return FALSE;
      }

      while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        // This is a weird case where not visible SKU does not have any related
        // configurable.
        if (trim(strtolower($data[$indexes['visibility']])) == 'not visible individually') {
          $not_visible_ind_skus[] = $data[$indexes['partnum']];
        }
      }
    }
    fclose($handle);

    if (empty($not_visible_ind_skus)) {
      $this->output()->writeln(dt('There is no SKU which are not visible individually as per the merchandising report.'));
      return;
    }

    $query = $this->connection->select('node__field_skus', 'n');
    $query->fields('n', ['entity_id']);
    $query->condition('n.bundle', 'acq_product');
    $query->condition('n.field_skus_value', $not_visible_ind_skus, 'IN');
    $nids = $query->execute()->fetchAllKeyed(0, 0);

    $this->output()->writeln(dt('@count product nodes are related to SKUs which are not supposed to be visible individually!nids', [
      '@count' => is_countable($nids) ? count($nids) : 0,
      '!nids' => $verbose ? ":\n'" . implode("','", $nids) . "'" : '.',
    ]));

    if (!empty($nids) && $this->io()->confirm(dt('Do you want to delete the @count product nodes?', [
      '@count' => is_countable($nids) ? count($nids) : 0,
    ]))) {
      $count = 1;
      foreach ($nids as $nid) {
        try {
          $storage = $this->entityTypeManager->getStorage('node');
          if ($node = $storage->load($nid)) {
            $node->delete();

            if ($count % 100 == 0) {
              $this->output()->writeln(dt("Delete @count/@total...", [
                '@count' => $count,
                '@total' => is_countable($nids) ? count($nids) : 0,
              ]));
            }
            $count++;
          }
        }
        catch (\Exception $e) {
          $this->drupalLogger->warning('Impossible to delete the product node @nid, exception: @message.', [
            '@nid' => $nid,
            '@exception' => $e->getMessage(),
          ]);
        }
      }
    }
  }

}
