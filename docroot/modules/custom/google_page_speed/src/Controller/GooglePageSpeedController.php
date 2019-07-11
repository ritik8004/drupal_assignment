<?php

namespace Drupal\google_page_speed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Component\Serialization\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\google_page_speed\Service\GpsInsightsWrapper;

/**
 * Returns responses for Google Page Speed integration routes.
 */
class GooglePageSpeedController extends ControllerBase {

  /**
   * The Database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The GpsInsightsWrapper service object.
   *
   * @var \Drupal\google_page_speed\Service\GpsInsightsWrapper
   */
  protected $gpsInsights;

  /**
   * Inject cache_tags.invalidator sservice.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container interface.
   *
   * @return static
   *   Injecting database and request_stack service objects.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('google_page_speed.gps_insights')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Injecting database connection.
   * @param \Drupal\google_page_speed\Service\GpsInsightsWrapper $gps_insights
   *   Injecting Gps Insights service.
   */
  public function __construct(Connection $database, GpsInsightsWrapper $gps_insights) {
    $this->database = $database;
    $this->gpsInsights = $gps_insights;
  }

  /**
   * Builds the response.
   *
   * @param int $metric_id
   *   Passing metric id as filter.
   * @param string $device
   *   Passing device type.
   * @param string $time
   *   Passing time period for which data is needed.
   *
   * @throws \Exception
   */
  public function getPageScore($metric_id = '', $device = 'desktop', $time = 'one-month') {

    $rows = [];
    $final_data = [];
    $timestamp = $this->gpsInsights->getTimeStamp($time);
    $query = $this->getSelectQuery($metric_id, $device, $timestamp);
    $query->fields('gps_ma', ['created', 'url_id']);
    $query->fields('gps_md', ['value']);
    $query->orderBy('gps_ma.created', 'ASC');
    $results = $query->execute()->fetchAll();
    foreach ($results as $result) {
      if (!isset($rows[$result->created][0])) {
        $rows[$result->created][0] = $result->created;
      }
      $rows[$result->created][intval($result->url_id)] = $result->value;
    }
    $row_counts = $this->getSelectQuery($metric_id, $device, $timestamp)
      ->distinct()
      ->execute()
      ->fetchAllKeyed(0, 0);

    // Initialising index 0. It will help in array_diff_keys.
    $row_counts[0] = 0;

    foreach ($rows as $row) {
      $results = array_diff_key($row_counts, $row);
      foreach ($results as $result) {
        $row[$result] = 0;
      }
      ksort($row);
      $i = 0;
      foreach ($row as $value) {
        $final[intval($i)] = floatval($value); $i++;
      }
      $final_data[] = $final;
    }

    $final_data = Json::encode($final_data);
    echo($final_data);
    die;

  }

  /**
   * To fetch the url lists in the given time.
   *
   * @param int $metric_id
   *   The metric id to search.
   * @param string $device
   *   The device to search for.
   * @param string $time
   *   The time to search for.
   *
   * @throws \Exception
   */
  public function getUrlList($metric_id, $device, $time) {
    $timestamp = $this->gpsInsights->getTimeStamp($time);
    $url_id_list = $this->getSelectQuery($metric_id, $device, $timestamp)
      ->execute()
      ->fetchAllKeyed(0, 0);

    $urls_list_select = $this->database->select('google_page_speed_url', 'gps_url');
    $urls_list_select->fields('gps_url', ['url_id', 'url']);
    $urls_list_select->condition('url_id', $url_id_list, 'IN');
    $url_list = $urls_list_select->execute()->fetchAllKeyed(0, 1);
    $url_list = Json::encode($url_list);
    echo $url_list;
    die;
  }

  /**
   * To prepare the common select query object.
   *
   * @param int $metric_id
   *   The metric id to search for.
   * @param string $device
   *   The device to search for.
   * @param int $timestamp
   *   The timestamp of the relative time to search for.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   The returned select query object.
   */
  protected function getSelectQuery($metric_id, $device, $timestamp) {
    $select_query = $this->database->select('google_page_speed_measure_attempts', 'gps_ma');
    $select_query->innerJoin('google_page_speed_measure_data', 'gps_md', 'gps_ma.measure_id = gps_md.measure_id');
    $select_query->fields('gps_ma', ['url_id'])
      ->condition('gps_ma.device', trim($device), '=')
      ->condition('gps_ma.created', [$timestamp, strtotime('now')], 'BETWEEN')
      ->condition('gps_ma.status', 1, '=')
      ->condition('gps_md.reference', trim($metric_id), '=');

    return $select_query;
  }

}
