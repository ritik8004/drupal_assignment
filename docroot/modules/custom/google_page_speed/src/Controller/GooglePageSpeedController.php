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
   */
  public function getPageScore($metric_id = '', $screen = 'desktop', $time = 'one-week') {

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

      default:
        $timestamp = strtotime('-1 week');
    }

    $rows = [];
    $final_data = [];
    $query = $this->database->select('google_page_speed_scores', 'gps');
    $query->fields('gps', ['created', 'value', 'url_id']);
    $query->condition('gps.metric_id', trim($metric_id), '=');
    $query->condition('gps.device', trim($screen), '=');
    $query->condition('gps.created', [$timestamp, strtotime('now')], 'BETWEEN');
    $query->orderBy('gps.url_id', 'ASC');
    $results = $query->execute()->fetchAll();
    foreach ($results as $result) {
      if ($rows[$result->created][0] != $result->created) {
        $rows[$result->created][0] = $result->created;
      }
      $rows[$result->created][] = $result->value;
    }

    $select_query = $this->database->select('google_page_speed_scores', 'gps');
    $select_query->fields('gps', ['url_id'])
      ->condition('gps.metric_id', trim($metric_id), '=')
      ->condition('gps.device', trim($screen), '=')
      ->condition('gps.created', [$timestamp, strtotime('now')], 'BETWEEN');
    $row_counts = $select_query->distinct()->countQuery()->execute()->fetchField();
    foreach ($rows as $row) {
      for ($i = 0; $i <= $row_counts; $i++) {
        $row[$i] = (isset($row[$i])) ? round(floatval($row[$i]), 2) : 0;
      }
      $final_data[] = $row;
    }
    $final_data = Json::encode($final_data);
    echo $final_data;
    die;

  }

  /**
   * Shows the data chart.
   *
   * @return array
   *   Returning renderable array.
   */
  public function showDataChart() {
    $build = [
      '#markup' => $this->t('See the Google PageSpeed Insights data in below chart.'),
    ];
    return $build;
  }

}
