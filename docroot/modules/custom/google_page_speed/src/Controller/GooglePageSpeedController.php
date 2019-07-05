<?php

namespace Drupal\google_page_speed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Component\Serialization\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The request stack object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

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
      $container->get('request_stack')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Injecting database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Injecting request.
   */
  public function __construct(Connection $database, RequestStack $request_stack) {
    $this->database = $database;
    $this->requestStack = $request_stack;
  }

  /**
   * Builds the response.
   *
   * @param int $metric_id
   *   Passing metric id as filter.
   * @param string $screen
   *   Passing screen type.
   * @param string $time
   *   Passing time period for which data is needed.
   *
   * @throws \Exception
   */
  public function getPageScore($metric_id = '', $screen = 'desktop', $time = 'one-month') {

    $rows = [];
    $final_data = [];
    $timestamp = $this->getTimeStamp($time);
    $query = $this->getSelectQuery($metric_id, $screen, $timestamp);
    $query->fields('gps_ma', ['created', 'url_id']);
    $query->fields('gps_md', ['value']);
    $query->orderBy('gps_ma.created', 'ASC');
    $results = $query->execute()->fetchAll();
    foreach ($results as $result) {
      if ($rows[$result->created][0] != $result->created) {
        $rows[$result->created][0] = $result->created;
      }
      $rows[$result->created][intval($result->url_id)] = $result->value;
    }
    $row_counts = $this->getSelectQuery($metric_id, $screen, $timestamp)
      ->distinct()
      ->countQuery()
      ->execute()
      ->fetchField();

    foreach ($rows as $row) {
      for ($i = 0; $i <= $row_counts; $i++) {
        $row[intval($i)] = (isset($row[intval($i)])) ? round(floatval($row[intval($i)]), 2) : 0;
      }
      ksort($row);
      $final_data[] = $row;
    }

    $final_data = Json::encode($final_data);
    echo $final_data;
    die;

  }

  /**
   * To fetch the url lists in the given time.
   *
   * @param int $metric_id
   *   The metric id to search.
   * @param string $screen
   *   The screen to search for.
   * @param string $time
   *   The time to search for.
   *
   * @throws \Exception
   */
  public function getUrlList($metric_id, $screen, $time) {
    $timestamp = $this->getTimeStamp($time);
    $url_id_list = $this->getSelectQuery($metric_id, $screen, $timestamp)
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
   * @param string $screen
   *   The screen to search for.
   * @param int $timestamp
   *   The timestamp of the relative time to search for.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   The returned select query object.
   */
  protected function getSelectQuery($metric_id, $screen, $timestamp) {
    $select_query = $this->database->select('google_page_speed_measure_attempts', 'gps_ma');
    $select_query->innerJoin('google_page_speed_measure_data', 'gps_md', 'gps_ma.measure_id = gps_md.measure_id');
    $select_query->fields('gps_ma', ['url_id'])
      ->condition('gps_ma.device', trim($screen), '=')
      ->condition('gps_ma.created', [$timestamp, strtotime('now')], 'BETWEEN')
      ->condition('gps_ma.status', 1, '=')
      ->condition('gps_md.reference', trim($metric_id), '=');

    return $select_query;
  }

  /**
   * To get timestamp based on time.
   *
   * @param string $time
   *   The time to search for.
   *
   * @return \DateTime|false|int
   *   The returning timestamp.
   *
   * @throws \Exception
   */
  protected function getTimeStamp($time) {
    switch ($time) {
      case 'one-week':
        $timestamp = strtotime('-1 week');
        break;

      case 'one-month':
        $timestamp = strtotime('-1 month');
        break;

      case 'one-year':
        $timestamp = strtotime('-1 year');
        break;

      case 'three-month':
        $timestamp = strtotime('-3 month');
        break;

      case 'all-time':
        $timestamp = 0;
        break;

      default:
        $timestamp = strtotime('-1 month');
    }

    return $timestamp;
  }

}
