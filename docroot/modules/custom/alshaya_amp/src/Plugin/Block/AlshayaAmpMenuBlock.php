<?php

namespace Drupal\alshaya_amp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides alshaya amp menu block.
 *
 * @Block(
 *   id = "alshaya_amp_menu",
 *   admin_label = @Translation("Alshaya AMP Menu")
 * )
 */
class AlshayaAmpMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Vocabulary id.
   *
   * @var string
   */
  protected $vid = 'acq_product_category';

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaMegaMenuBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language
   *   The Language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection, LanguageManagerInterface $language) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
    $this->languageManager = $language;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = [];
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid', 'name']);
    $query->innerJoin('taxonomy_term_hierarchy', 'tth', 'tth.tid=tfd.tid');
    $query->condition('tth.parent', 0);
    $query->condition('tfd.vid', $this->vid);
    $query->condition('tfd.langcode', $this->languageManager->getCurrentLanguage()->getId());
    $terms = $query->execute()->fetchAll();
    if (!empty($terms)) {
      foreach ($terms as $term) {
        $data[] = [
          'tid' => $term->tid,
          'name' => $term->name,
        ];
      }
    }

    return [
      '#theme' => 'alshaya_amp_menu',
      '#data' => $data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['term_list']);
  }

}
