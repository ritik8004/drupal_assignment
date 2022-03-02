<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\CategoryRepositoryInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\node\NodeInterface;
use Drupal\Core\Database\Connection;
use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\taxonomy\TermInterface;

/**
 * Class Product Category Mapping Manager.
 *
 * @package Drupal\alshaya_acm_product_category\Service
 */
class ProductCategoryMappingManager {

  use LoggerChannelTrait;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Category Repository.
   *
   * @var \Drupal\acq_sku\CategoryRepositoryInterface
   */
  protected $categoryRepo;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Conductor Ingest API Helper.
   *
   * @var \Drupal\acq_commerce\Conductor\IngestAPIWrapper
   */
  protected $ingestApi;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  protected $i18nHelper;

  /**
   * ProductCategoryMappingManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\acq_sku\CategoryRepositoryInterface $category_repo
   *   Category Repository.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\acq_commerce\Conductor\IngestAPIWrapper $ingest_api
   *   IngestAPI manager interface.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   */
  public function __construct(SkuManager $sku_manager,
                              CategoryRepositoryInterface $category_repo,
                              Connection $connection,
                              IngestAPIWrapper $ingest_api,
                              I18nHelper $i18n_helper) {
    $this->skuManager = $sku_manager;
    $this->categoryRepo = $category_repo;

    $this->logger = $this->getLogger('ProductCategoryMappingManager');
    $this->connection = $connection;
    $this->ingestApi = $ingest_api;
    $this->i18nHelper = $i18n_helper;
  }

  /**
   * Wrapper function to map categories to product.
   *
   * @param string $sku
   *   SKU as string.
   * @param array $category_commerce_ids
   *   Commerce Category IDs.
   */
  public function mapCategoriesToProduct(string $sku, array $category_commerce_ids) {
    $sku_entity = SKU::loadFromSku($sku);
    if (!($sku_entity instanceof SKUInterface)) {
      $this->logger->warning('Skipped mapping update as SKU entity not available for SKU @sku', [
        '@sku' => $sku,
      ]);
      return;
    }

    $node = $this->skuManager->getDisplayNode($sku_entity, FALSE);
    if (!($node instanceof NodeInterface)) {
      $this->logger->warning('Skipped mapping update as Node not available for SKU @sku', [
        '@sku' => $sku,
      ]);
      return;
    }

    $categories_new = $this->categoryRepo->getTermIdsFromCommerceIds($category_commerce_ids);
    $categories_existing = array_column($node->get('field_category_original')->getValue(), 'target_id');

    if (empty($categories_new) && empty($categories_existing)) {
      // Skip if both are empty.
      $this->logger->notice('Category mapping skipped as both existing and new are empty for SKU @sku', [
        '@sku' => $sku,
      ]);
      return;
    }
    elseif (empty($categories_new)
      || empty($categories_existing)
      || array_diff($categories_new, $categories_existing)
      || array_diff($categories_existing, $categories_new)) {
      // Do nothing here, we will update.
    }
    else {
      // No diff found, we skip.
      $this->logger->notice('Category mapping skipped as there is no change for SKU @sku; New: @new; Existing: @existing', [
        '@sku' => $sku,
        '@new' => implode(',', $categories_new),
        '@existing' => implode(',', $categories_existing),
      ]);

      return;
    }

    // Check if category not exist in drupal.
    $category_not_exist = array_diff($category_commerce_ids, array_keys($categories_new));
    foreach ($category_not_exist as $key => $value) {
      $this->logger->notice('Category @cat does not exist for SKU @sku in drupal', [
        '@cat' => $value,
        '@sku' => $sku,
      ]);

      $this->connection->merge('sku_category_map')
        ->key('sku', $sku)
        ->key('mdc_category_id', $value)
        ->fields([
          'sku' => $sku,
          'mdc_category_id' => $value,
        ])->execute();
    }

    $node->get('field_category')->setValue($categories_new);
    $node->get('field_category_original')->setValue($node->get('field_category')->getValue());
    $node->save();

    // Trigger update to ensure all post updated event subscribers
    // are invoked.
    _alshaya_acm_product_post_sku_operation($sku_entity, ProductUpdatedEvent::EVENT_UPDATE);

    $this->logger->notice('Category mapping updated for SKU @sku; New: @new; Existing: @existing', [
      '@sku' => $sku,
      '@new' => implode(',', $categories_new),
      '@existing' => implode(',', $categories_existing),
    ]);
  }

  /**
   * Wrapper function to map categories to product.
   *
   * @param Drupal\taxonomy\TermInterface $term
   *   Term as Object.
   */
  public function mapSkuCategory(TermInterface $term) {
    $commerce_id = NULL;
    $commerce_id = $term->get('field_commerce_id')->value;

    $skus = [];
    if ($commerce_id) {
      $query = $this->connection->select('sku_category_map', 'scm');
      $query->fields('scm', ['sku', 'mdc_category_id ']);
      $query->condition('scm.mdc_category_id', $commerce_id, '=');
      $result = $query->execute()->fetchAll();
      foreach ($result as $key => $value) {
        $skus[] = $value->sku;
      }
    }
    if (!empty($skus)) {
      foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
        foreach (array_chunk($skus, 6) as $chunk) {
          $this->ingestApi->productFullSync($store_id, $langcode, implode(',', $chunk), '', 2);
          $this->logger->notice('SKUs are syncd from category mapping table @sku, @mdc and drupal category @cat for Language @lang', [
            '@sku' => implode(',', $chunk),
            '@mdc' => $commerce_id,
            '@cat' => $term->id(),
            '@lang' => $langcode,
          ]);
        }
      }
      $row_deleted = $this->connection->delete('sku_category_map')
        ->condition('mdc_category_id', $commerce_id)
        ->execute();
    }
  }

}
