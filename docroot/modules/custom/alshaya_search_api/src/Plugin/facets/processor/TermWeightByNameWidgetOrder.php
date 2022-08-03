<?php

namespace Drupal\alshaya_search_api\Plugin\facets\processor;

use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\Processor\SortProcessorPluginBase;
use Drupal\facets\Result\Result;
use Drupal\facets\FacetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  protected $connection;

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
    if (empty($this->getConfiguration()['attribute_id'])) {
      return 0;
    }

    $query = $this->connection->select('taxonomy_term_field_data', 'td');
    $query->join('taxonomy_term__field_sku_attribute_code', 'sac', 'td.tid = sac.entity_id');
    $query->fields('td', ['name', 'weight']);
    $query->condition('td.vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY, '=');
    $query->condition('td.name', [$a->getRawValue(), $b->getRawValue()], 'IN');
    $query->condition('sac.field_sku_attribute_code_value', $this->getConfiguration()['attribute_id']);
    $query->condition('td.langcode', $this->languageManager->getCurrentLanguage()->getId());
    $result = $query->execute()->fetchAllKeyed();

    // Incase if any of the arguments don't have raw value.
    if ((is_countable($result) ? count($result) : 0) < 2) {
      return 0;
    }

    return ($result[$a->getRawValue()] < $result[$b->getRawValue()]) ? -1 : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $build = parent::buildConfigurationForm($form, $form_state, $facet);

    $build['attribute_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Attribute ID'),
      '#default_value' => $this->getConfiguration()['attribute_id'] ?? '',
      '#description' => $this->t('This should contain the attribute id name which is being used for this facet.'),
    ];

    return $build;
  }

}
