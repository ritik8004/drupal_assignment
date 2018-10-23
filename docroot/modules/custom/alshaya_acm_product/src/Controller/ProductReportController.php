<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class ProductReportController.
 *
 * @package Drupal\alshaya_acm_product\Controller
 */
class ProductReportController extends ControllerBase {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('file_system')
    );
  }

  /**
   * ProductReportController constructor.
   *
   * @param \Drupal\Core\Database\Driver\mysql\Connection $database
   *   Database object.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The filesystem service.
   */
  public function __construct(Connection $database, FileSystemInterface $fileSystem) {
    $this->database = $database;
    $this->fileSystem = $fileSystem;
  }

  /**
   * Controller to download Product Report.
   */
  public function downloadReport() {
    $select = $this->database->select('acq_sku_field_data');
    $select->fields('acq_sku_field_data', [
      'sku',
      'product_id',
      'langcode',
      'type',
      'stock',
      'price',
      'special_price',
      'final_price',
    ]);
    $select->orderBy('sku', 'ASC');
    $skus = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $path = file_create_url($this->fileSystem->realpath("temporary://"));
    $fp = fopen($path . '/export-sku-data.csv', 'w');
    fwrite($fp, 'SKU, Type, Language, Product ID, Stock, Price, Final Price, Special Price' . "\n");
    foreach ($skus as $sku) {
      fwrite($fp, $sku['sku'] . ',' . $sku['type'] . ',' . $sku['langcode'] . ',' . $sku['product_id'] . ',' . $sku['stock'] . ',' . $sku['price'] . ',' . $sku['final_price'] . ',' . $sku['special_price'] . "\n");
    }

    fclose($fp);

    $filename = $path . '/export-sku-data.csv';
    $response = new BinaryFileResponse($filename);
    $site_name = $this->config('system.site')->get('name');
    $disposition = $response->headers->makeDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      'Product-report' . $site_name . '.csv'
    );
    $response->headers->set('Content-Disposition', $disposition);
    return $response;
  }

}
