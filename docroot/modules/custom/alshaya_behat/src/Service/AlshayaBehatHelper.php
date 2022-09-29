<?php

namespace Drupal\alshaya_behat\Service;

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
   * Constructor for Handlebars Service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Connection.
   * @param \Symfony\Component\HttpKernel\HttpKernel $http_kernel
   *   Http kernel.
   */
  public function __construct(
    Connection $connection,
    HttpKernel $http_kernel
  ) {
    $this->database = $connection;
    $this->httpKernel = $http_kernel;
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
  public function isNodePageLoading(NodeInterface $node) {
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
  public function getSkus($page, $limit, $oos = FALSE) {
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

}
