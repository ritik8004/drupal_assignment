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
   */
  public function getPageScore($screen = 'desktop') {
    $url = $this->requestStack->getCurrentRequest()->query->get('url');
    $rows = [];
    $query = $this->database->select('google_page_speed_data', 'gps');
    $query->fields('gps', ['created', 'score']);
    $query->condition('gps.url', trim($url), '=');
    $query->condition('gps.screen', trim($screen), '=');
    $query->orderBy('gps.created', 'DESC');
    $results = $query->execute()->fetchAll();

    foreach ($results as $result) {
      $scores = Json::decode($result->score);
      $rows[] = [
        $result->created,
        $scores[0],
        $scores[1],
        $scores[2],
        $scores[3],
        $scores[4],
        $scores[5],
      ];
    }

    $rows = Json::encode($rows);
    echo $rows;
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
      '#markup' => $this->t('The PageSpeed score related to the configured URLs can be seen here.'),
    ];
    return $build;
  }

}
