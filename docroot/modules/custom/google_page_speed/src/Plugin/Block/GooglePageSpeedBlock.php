<?php

namespace Drupal\google_page_speed\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * Provides a 'Google Page Speed Display' block.
 *
 * @property \Drupal\Core\Cache\CacheTagsInvalidatorInterface cacheInvalidator
 * @Block(
 *   id = "google_page_speed_block",
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
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->cacheInvalidator->invalidateTags(['google-page-speed:block']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'google_page_speed_block';

    $build['#block_title'] = $this->label();
    $build['#cache'] = [
      'tags' => ['google-page-speed:block'],
    ];

    $build['#urls'] = $this->getTestedUrls();

    return $build;
  }

  /**
   * This function fetches the distinct urls from the database.
   *
   * @return mixed
   *   Returning distinct url values from the database.
   */
  public function getTestedUrls() {
    $query = $this->database->select('google_page_speed_data', 'gps');
    $query->fields('gps', ['url']);
    $query->distinct();
    $urls = $query->execute()->fetchAllKeyed(0, 0);
    return $urls;
  }

}
