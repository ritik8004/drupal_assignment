<?php
namespace Drupal\acq_sku\Commands;

use Drupal\acq_commerce\Conductor\APIWrapperInterface;
use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\ConductorCategoryManager;
use Drupal\acq_sku\Plugin\rest\resource\ProductSyncResource;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

class AcqSkuDrushCommands extends DrushCommands {

  /**
   * Api Wrapper service.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapperInterface
   */
  private $apiWrapper;

  /**
   * i18nHelper service.
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
   * AcqSkuDrushCommands constructor.
   *
   * @param APIWrapperInterface $apiWrapper
   *   Commerce Api Wrapper.
   * @param I18nHelper $i18nHelper
   *  i18nHelper service.
   * @param IngestAPIWrapper $ingestAPIWrapper
   *   Ingest Api Wrapper service.
   * @param ConductorCategoryManager $conductorCategoryManager
   *   Conductor category manager service.
   * @param ProductOptionsManager $productOptionsManager
   *   Product Options Manager service.
   * @param LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Channel Factory service.
   */
  public function __construct(APIWrapperInterface $apiWrapper,
                              I18nHelper $i18nHelper,
                              IngestAPIWrapper $ingestAPIWrapper,
                              ConductorCategoryManager $conductorCategoryManager,
                              ProductOptionsManager $productOptionsManager,
                              LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->apiWrapper = $apiWrapper;
    $this->i18nhelper = $i18nHelper;
    $this->ingestApiWrapper = $ingestAPIWrapper;
    $this->conductorCategoryManager = $conductorCategoryManager;
    $this->productOptionsManager = $productOptionsManager;
    $this->logger = $loggerChannelFactory->get('acq_sku');
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
      $confirm = dt('Are you sure you want to import all products for @language language?', [
        '@language' => $langcode,
      ]);

      if (!$this->io->confirm($confirm)) {
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
    $this->conductorCategoryManager->synchronizeTree('acq_product_category');
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
    \Drupal::logger('acq_sku')->notice('Synchronizing all commerce product options, please wait...');
    $this->productOptionsManager->synchronizeProductOptions();
    \Drupal::logger('acq_sku')->notice('Product attribute sync completed.');
  }

  /**
   * Run a partial synchronization of commerce product records synchronously for testing / dev.
   *
   * @command acq_sku:sync-products-test
   *
   * @param integer $count
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

}