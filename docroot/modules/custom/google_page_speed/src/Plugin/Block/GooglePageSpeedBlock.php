<?php

namespace Drupal\google_page_speed\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * Provides a 'Google Page Speed Display' block.
 *
 * @property \Drupal\Core\Cache\CacheTagsInvalidatorInterface cacheInvalidator
 * @Block(
 *   id = "googlepagespeedblock",
 *   admin_label = @Translation("Google Page Speed Block"),
 * )
 */
class GooglePageSpeedBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Database Connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Inject cache_tags.invalidator service.
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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache_tags.invalidator'),
      $container->get('database')
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
   * @param \Drupal\Core\Database\Connection $database
   *   Injecting database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CacheTagsInvalidatorInterface $cacheTagsInvalidator, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cacheInvalidator = $cacheTagsInvalidator;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'google_page_speed_block';
    $build['#block_title'] = $this->label();
    $build['#metrics'] = $this->getMetrics();
    $build['#attached']['library'] = [
      'google_page_speed/google-chart-loader',
      'google_page_speed/google_page_speed',
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(
      parent::getCacheTags(),
      ['google-page-speed:data']
    );
  }

  /**
   * This function fetches the distinct urls from the database.
   *
   * @return mixed
   *   Returning distinct metrics from the database.
   */
  protected function getMetrics() {
    $query = $this->database->select('google_page_speed_measure_data', 'gps_md');
    $query->fields('gps_md', ['reference']);
    $query->distinct();
    $metrics = $query->execute()->fetchAllKeyed(0, 0);
    return $metrics;
  }

}
