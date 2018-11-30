<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;

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
   * Current time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $currentTime;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('file_system'),
      $container->get('datetime.time'),
      $container->get('date.formatter')
    );
  }

  /**
   * ProductReportController constructor.
   *
   * @param \Drupal\Core\Database\Driver\mysql\Connection $database
   *   Database object.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The filesystem service.
   * @param \Drupal\Component\Datetime\TimeInterface $current_time
   *   Current time service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter service.
   */
  public function __construct(Connection $database,
                              FileSystemInterface $fileSystem,
                              TimeInterface $current_time,
                              DateFormatterInterface $date_formatter) {
    $this->database = $database;
    $this->fileSystem = $fileSystem;
    $this->currentTime = $current_time;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * Controller to download Product Report.
   */
  public function downloadReport() {
    $path = file_create_url($this->fileSystem->realpath("temporary://"));
    // @codingStandardsIgnoreLine
    global $acsf_site_name;
    $time_format = $this->dateFormatter->format($this->currentTime->getRequestTime(), 'custom', 'Ymd');
    $filename = 'product-report-' . $acsf_site_name . '-' . $time_format . '.csv';

    $fp = fopen($path . '/' . $filename, 'w');
    fwrite($fp, 'SKU, Type, Language, Product ID, Stock, Price, Final Price, Special Price' . "\n");

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
    $result = $select->execute();

    while (($sku = $result->fetchAssoc()) !== FALSE) {
      fwrite($fp, $sku['sku'] . ',' . $sku['type'] . ',' . $sku['langcode'] . ',' . $sku['product_id'] . ',' . $sku['stock'] . ',' . $sku['price'] . ',' . $sku['final_price'] . ',' . $sku['special_price'] . "\n");
    }

    fclose($fp);

    $response = new BinaryFileResponse($path . '/' . $filename);

    $disposition = $response->headers->makeDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      $filename
    );
    $response->headers->set('Content-Disposition', $disposition);
    return $response;
  }

}
