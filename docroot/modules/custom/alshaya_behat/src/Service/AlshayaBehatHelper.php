<?php

namespace Drupal\alshaya_behat\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\StockManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Database\Connection;
use Drupal\node\NodeInterface;
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
   */
  public function __construct(
    Connection $connection,
    HttpKernel $http_kernel,
    StockManager $stock_manager,
    SkuManager $sku_manager,
  ) {
    $this->database = $connection;
    $this->httpKernel = $http_kernel;
    $this->stockManager = $stock_manager;
    $this->skuManager = $sku_manager;
  }

  /**
   * Checks if node page loads successfully or not.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   *
   * @return bool
   *   TRUE if node loads successfully else false.
   */
  private function isNodePageLoading(NodeInterface $node) {
    $request = Request::create('/node/' . $node->id());
    $request_success = TRUE;

    try {
      $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
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
  private function getSkus($page, $limit, $oos = FALSE) {
    // Query the database to fetch in-stock products.
    $query = $this->database->select('node__field_skus', 'nfs');
    $query->leftJoin('acq_sku_stock', 'stock', 'stock.sku = nfs.field_skus_value');

    if ($oos) {
      $query->condition('quantity', '0', '=');
    }
    else {
      $query->condition('quantity', '0', '>');
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
  public function getWorkingProduct($oos = FALSE) {
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
        if (!$this->isNodePageLoading($node)) {
          continue;
        }
        return $node;
      }

      $page++;
    }
  }

}
