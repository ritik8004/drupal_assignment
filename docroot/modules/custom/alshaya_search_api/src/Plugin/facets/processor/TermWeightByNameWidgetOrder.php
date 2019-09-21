<?php

namespace Drupal\alshaya_search_api\Plugin\facets\processor;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\Processor\SortProcessorPluginBase;
use Drupal\facets\Result\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Database\Connection;

/**
 * A processor that orders the terms by their weight using term name.
 *
 * @FacetsProcessor(
 *   id = "term_weight_by_name_widget_order",
 *   label = @Translation("Sort by taxonomy term weight according to term name"),
 *   description = @Translation("Sorts the widget results by taxonomy term weight according to term name. This sort is only applicable for term-based facets."),
 *   stages = {
 *     "sort" = 60
 *   }
 * )
 */
class TermWeightByNameWidgetOrder extends SortProcessorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Constructs a new object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->languageManager = $language_manager;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function sortResults(Result $a, Result $b) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->connection->select('taxonomy_term_field_data', 'td');
    $query->join('taxonomy_term__field_sku_attribute_code', 'sac', 'td.tid = sac.entity_id');
    $result = $query
      ->fields('td', ['name', 'weight'])
      ->condition('td.vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY, '=')
      ->condition('td.name', [$a->getRawValue(), $b->getRawValue()], 'IN', [':sizea' => $a->getRawValue(), ':sizeb' => $b->getRawValue()])
      ->condition('sac.field_sku_attribute_code_value', 'size_textile_eu')
      ->condition('td.langcode', $langcode)
      ->execute()
      ->fetchAllKeyed();
    // Incase if any of the arguments don't have raw value.
    if (count($result) < 2) {
      return 0;
    }

    return ($result[$a->getRawValue()] < $result[$b->getRawValue()]) ? -1 : 1;
  }

}
