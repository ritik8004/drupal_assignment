<?php

namespace Drupal\alshaya_product_options\Plugin\facets\processor;

use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sort Attributes based on remote system positions.
 *
 * @FacetsProcessor(
 *   id = "sort_attributes_facets_processor",
 *   label = @Translation("Sort Attributes"),
 *   description = @Translation("Sort based on remote system positions."),
 *   stages = {
 *     "build" = 999
 *   }
 * )
 */
class SortAttributesResultProcessor extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * Current language code.
   *
   * @var string
   */
  private $currentLanguageCode;

  /**
   * SortAttributesResultProcessor constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Database\Connection $database
   *   Database Connection.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              Connection $database,
                              LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->database = $database;
    $this->currentLanguageCode = $language_manager->getCurrentLanguage()->getId();
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
  public function build(FacetInterface $facet, array $results) {
    if (empty($results)) {
      return $results;
    }

    // Result items in facet.
    $order = $this->getAttributesOrder($facet);

    usort($results, function ($a, $b) use ($order) {
      // If it is simply an ID for which we don't have taxonomy term
      // lets display that in bottom.
      $order_a = $order[$a->getRawValue()] ?? 9999999;
      $order_b = $order[$b->getRawValue()] ?? 9999999;
      return ($order_a < $order_b) ? -1 : 1;
    });

    $facet->setResults($results);

    return $results;
  }

  /**
   * Get attribute code for facet.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   Facet.
   *
   * @return string
   *   Attribute code.
   */
  private function getAttributeFromIdentifier(FacetInterface $facet) {
    return str_replace('attr_', '', $facet->getFieldIdentifier());
  }

  /**
   * Get weight of attributes.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   Facet.
   *
   * @return array
   *   Attribute name as key and weight as value.
   */
  private function getAttributesOrder(FacetInterface $facet) {
    $attribute_code = $this->getAttributeFromIdentifier($facet);

    $facet_results = [];
    foreach ($facet->getResults() as $result) {
      $facet_results[] = $result->getRawValue();
    }

    $query = $this->database->select('taxonomy_term_field_data', 'ttfd');
    $query->fields('ttfd', ['name', 'weight']);
    $query->condition('ttfd.name', $facet_results, 'IN');
    $query->condition('ttfd.langcode', $this->currentLanguageCode);

    $query->join('taxonomy_term__field_sku_attribute_code', 'ttfsac', 'ttfsac.entity_id = ttfd.tid');
    $query->condition('ttfsac.field_sku_attribute_code_value', $attribute_code);
    $query->distinct();
    $query->orderBy('weight', 'ASC');
    $results = $query->execute()->fetchAllKeyed(0, 1);

    return $results;
  }

}
