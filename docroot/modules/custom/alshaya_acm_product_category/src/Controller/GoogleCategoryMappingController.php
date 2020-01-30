<?php

namespace Drupal\alshaya_acm_product_category\Controller;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * Class GoogleCategoryMappingController.
 *
 * @package Drupal\alshaya_acm_product_category\Controller
 */
class GoogleCategoryMappingController extends ControllerBase {
  /**
   * Category vid.
   */
  const CATEGORY_VID = 'acq_product_category';

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
   * Controller to download google category mapping.
   */
  public function downloadGoogleCategoryMapping() {
    $path = file_create_url($this->fileSystem->realpath("temporary://"));
    // @codingStandardsIgnoreLine
    global $_acsf_site_name;
    $time_format = $this->dateFormatter->format($this->currentTime->getRequestTime(), 'custom', 'Ymd');
    $filename = 'google-category-mapping-' . $_acsf_site_name . '-' . $time_format . '.csv';

    $fp = fopen($path . '/' . $filename, 'w');
    fputcsv($fp, ['Commerce ID', 'Drupal ID', 'Name', 'Google Term']);

    // Select only those categories which are mapped with products.
    $select = $this->database->select('taxonomy_term_field_data', 'ttfd');
    $select->fields('ttfd', ['tid', 'name']);
    $select->fields('ttfcg', ['field_category_google_value']);
    $select->fields('ttfci', ['field_commerce_id_value']);
    $select->leftjoin('taxonomy_term__field_category_google', 'ttfcg', 'ttfcg.entity_id = ttfd.tid');
    $select->join('taxonomy_term__field_commerce_id', 'ttfci', 'ttfci.entity_id = ttfd.tid');
    $select->join('node__field_category', 'nfc', 'ttfd.tid = nfc.field_category_target_id');
    $select->condition('ttfd.langcode', 'en');
    $select->condition('ttfd.status', 1);
    $select->groupBy('nfc.field_category_target_id');
    $select->orderBy('ttfci.field_commerce_id_value', 'asc');
    $result = $select->execute();

    while (($sku = $result->fetchAssoc()) !== FALSE) {
      fputcsv(
        $fp,
        [
          $sku['field_commerce_id_value'],
          $sku['tid'],
          $sku['name'],
          $sku['field_category_google_value'],
        ]
      );
    }

    fclose($fp);

    $response = new BinaryFileResponse($path . '/' . $filename);

    $disposition = $response->headers->makeDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      $filename
    );

    $response->headers->set('X-Drupal-Cache-Tags', 'taxonomy_term:acq_product_category');
    $response->headers->set('Content-Disposition', $disposition);
    return $response;
  }

}
