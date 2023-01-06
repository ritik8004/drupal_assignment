<?php

namespace Drupal\alshaya_behat\Service;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\StockManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Service for Alshaya Behat.
 */
class AlshayaBehatHelper {

  /**
   * Database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * HTTP Kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernel
   */
  protected $httpKernel;

  /**
   * Stock Manager.
   *
   * @var \Drupal\acq_sku\StockManager
   */
  private $stockManager;

  /**
   * Sku manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected EntityRepositoryInterface $entityRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * The api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApi;


  /**
   * Number of skus to fetch.
   *
   * @var int
   */
  protected const SKUS_LIMIT = 10;

  /**
   * Constructor for Handlebars Service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection.
   * @param \Symfony\Component\HttpKernel\HttpKernel $http_kernel
   *   Http kernel.
   * @param \Drupal\acq_sku\StockManager $stock_manager
   *   Stock manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Sku manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity storage.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The acm api wrapper.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api
   *   The mdc api wrapper.
   */
  public function __construct(
    Connection $connection,
    HttpKernel $http_kernel,
    StockManager $stock_manager,
    SkuManager $sku_manager,
    EntityRepositoryInterface $entity_repository,
    EntityTypeManagerInterface $entity_type_manager,
    APIWrapper $api_wrapper,
    AlshayaApiWrapper $alshaya_api
  ) {
    $this->database = $connection;
    $this->httpKernel = $http_kernel;
    $this->stockManager = $stock_manager;
    $this->skuManager = $sku_manager;
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
    $this->apiWrapper = $api_wrapper;
    $this->alshayaApi = $alshaya_api;
  }

  /**
   * Checks if node page loads successfully or not.
   *
   * @param string $path
   *   Path to entity.
   *
   * @return bool
   *   TRUE if node loads successfully else false.
   */
  private function isEntityPageLoading(string $path): bool {
    $request = Request::create($path);

    try {
      $res = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
      $request_success = $res->getStatusCode() === 200;
    }
    catch (\Exception) {
      $request_success = FALSE;
    }

    return $request_success;
  }

  /**
   * Get SKUs.
   *
   * @param int $page
   *   Page number for query.
   * @param int $limit
   *   Number of skus to fetch.
   * @param bool $oos
   *   Whether to fetch OOS skus or not.
   *
   * @return array
   *   Array of SKU values.
   */
  private function getSkus($page, $limit, $oos = FALSE): array {
    // Query the database to fetch in-stock products.
    $query = $this->database->select('node__field_skus', 'nfs');
    $query->leftJoin('acq_sku_stock', 'stock', 'stock.sku = nfs.field_skus_value');

    if ($oos) {
      $query->condition('status', '0');
    }
    else {
      $query->condition('status', '1');
    }

    $query->fields('stock', ['sku']);
    $query->range($page * $limit, $limit);

    return $query->distinct()->execute()->fetchCol();
  }

  /**
   * Get a working OOS/In stock product.
   *
   * @param bool $oos
   *   If OOS product is required, set this to TRUE.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node object else null.
   */
  public function getWorkingProduct($oos = FALSE): ?NodeInterface {
    $page = 0;
    while (TRUE) {
      // Query the database to fetch in-stock products.
      $skus = $this->getSkus($page, self::SKUS_LIMIT, $oos);
      if (empty($skus)) {
        break;
      }

      foreach ($skus as $sku) {
        // Load the SKU.
        $main_sku = SKU::loadFromSku($sku);
        // If product is not buyable, use a different product.
        // We check here instead of adding condition to the db query since we
        // have to check the global configuration also.
        if (!alshaya_acm_product_is_buyable($main_sku)) {
          continue;
        }
        $is_product_in_stock = $this->stockManager->isProductInStock($main_sku);
        $condition = $oos === FALSE ? $is_product_in_stock : !$is_product_in_stock;
        if ($condition) {
          // SKU might be configurable. So fetch the parent.
          $parent_sku = $this->skuManager->getParentSkuBySku($main_sku);
          if ($parent_sku) {
            $is_product_in_stock = $this->stockManager->isProductInStock($parent_sku);
            $condition = $oos === FALSE ? $is_product_in_stock : !$is_product_in_stock;

            if ($condition) {
              $main_sku = $parent_sku;
            }
            else {
              continue;
            }
          }
        }
        else {
          continue;
        }

        // Fetch the display node for the SKU.
        $node = $this->skuManager->getDisplayNode($main_sku);
        if (is_null($node)) {
          continue;
        }
        // Request the node and check if there is any error when loading the
        // node.
        // If there is an error we check the next sku.
        if (!$this->isEntityPageLoading('/node/' . $node->id())) {
          continue;
        }
        return $node;
      }

      $page++;
    }

    return NULL;
  }

  /**
   * Query enabled categories with in-stock products.
   *
   * @param int $page
   *   Page number for query.
   *
   * @return array
   *   Array of category term ids.
   */
  private function getCategories(int $page): array {
    // Query the database to fetch categories with in-stock products.
    $query = $this->database->select('taxonomy_term__field_commerce_status', 'fcs');
    $query->leftJoin('node__field_category', 'fc', 'fcs.entity_id = fc.field_category_target_id');
    $query->leftJoin('node__field_skus', 'fs', 'fc.entity_id = fs.entity_id');
    $query->leftJoin('acq_sku_stock', 'stock', 'stock.sku = fs.field_skus_value');

    // Category is enabled.
    $query->condition('fcs.field_commerce_status_value', '1');

    // Category has in-stock SKUs.
    $query->condition('stock.status', '1');

    $query->fields('fcs', ['entity_id']);
    $query->range($page * self::SKUS_LIMIT, self::SKUS_LIMIT);

    return $query->distinct()->execute()->fetchCol();
  }

  /**
   * Get a working product listing.
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|Term
   *   The category object else null.
   */
  public function getWorkingCategory(): EntityInterface|EntityBase|Term|null {
    $page = 0;
    $taxonomy_term_trans = NULL;
    // Fetch all categories that are parent of some other categories.
    $query = $this->database->select('taxonomy_term__parent', 'parents');
    $query->fields('parents', ['parent_target_id']);
    $all_parent_term_ids = $query->distinct()->execute()->fetchCol();

    while (TRUE) {
      // Query the database to fetch categories with in-stock products.
      $categories = $this->getCategories($page);
      if (empty($categories)) {
        break;
      }
      foreach ($categories as $category) {
        // Skip if category is a parent or if its not loading properly.
        if (in_array($category, $all_parent_term_ids) || !$this->isEntityPageLoading("/taxonomy/term/$category")) {
          continue;
        }
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($category);
        // Get the current language translated category.
        if (!empty($term)) {
          $taxonomy_term_trans = $this->entityRepository->getTranslationFromContext($term);
        }
        if (empty($term) || empty($taxonomy_term_trans)) {
          continue;
        }

        return $taxonomy_term_trans;
      }
      $page++;
    }

    return NULL;
  }

  /**
   * Provides the in-stock Product having a promotion.
   *
   * @param string $type
   *   Promotion types like groupn, groupn_disc etc.
   *
   * @return \Drupal\node\NodeInterface|string
   *   The node object else error message.
   */
  public function getWorkingProductWithPromo(string $type): NodeInterface|string {
    // ACM API to fetch all promotion details.
    $promotions = $this->apiWrapper->getPromotions('cart');
    $promotions = is_array($promotions) ? $promotions : [];
    $promo_types = array_column($promotions, 'action');

    // If no promotion available of given type.
    if (empty($promotions) || !in_array($type, $promo_types)) {
      return 'No in-stock products found with given promotion type.';
    }

    // List of products having the promotion associated with.
    $products = $promotions[array_search($type, $promo_types)]['products'] ?? [];
    foreach ($products as $item) {
      if (!empty($item['product_sku'])) {
        // MDC API to fetch SKU details.
        $product_details = $this->alshayaApi->getSku($item['product_sku']);
        if (!empty($product_details)
          && !empty($product_details['name'])
          && !empty($product_details['custom_attributes'])
          && $product_details['status'] === 1
          && $product_details['extension_attributes']['stock_item']['is_in_stock'] === TRUE
        ) {
          // @todo Build product url from MDC API for V3 using GraphQL, independent of drupal DB.
          $node = $this->skuManager->getDisplayNode($item['product_sku'], FALSE);
          if ($node instanceof NodeInterface && $this->isEntityPageLoading($node->toUrl()->toString())) {
            return $node;
          }
          else {
            return 'Available product not found in Drupal Application. Probable sync issue.';
          }
        }
      }
    }

    return 'No in-stock products found with given promotion type.';
  }

}
