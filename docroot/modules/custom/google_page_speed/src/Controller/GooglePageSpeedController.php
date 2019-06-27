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
   * @var Connection
   */
  protected $database;

  /**
   * @var RequestStack
   */
  protected $requestStack;

  /**
   * Inject cache_tags.invalidator sservice.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container interface.
   * @param array $configuration
   *   Plugin configs.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   *
   * @return static
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
   * @param array $configuration
   *   Plugin configs.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   Injecting CacheTagsInvalidatorInterface.
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
    $query->fields('gps', ['created','score']);
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
        $scores[5]
      ];
    }

    $rows = Json::encode($rows);
    echo $rows;
    die;

  }

}
