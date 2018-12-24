<?php

namespace Drupal\acq_sku\Plugin\facets\processor;

use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\facet_source\SearchApiDisplay;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Removes the taxonomy terms from facet items which are not included in menu.
 *
 * @FacetsProcessor(
 *   id = "hide_taxonomy_not_in_menu",
 *   label = @Translation("Hide Taxonomy items not in Menu."),
 *   description = @Translation("Hides the taxonomy terms not included in menu."),
 *   stages = {
 *     "build" = 5
 *   }
 * )
 */
class HideTaxonomyNotInMenu extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * DB Connection.
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
   *   The language manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   DB Connection.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              LanguageManagerInterface $language_manager,
                              Connection $connection) {
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
  public function build(FacetInterface $facet, array $results) {
    // Don't do anything if results set is empty. We can face this case
    // when search results are empty.
    if (empty($results)) {
      return $results;
    }

    $source = $facet->getFacetSource();

    // Support multiple entity types when using Search API.
    if (($source instanceof SearchApiDisplay) &&
      ($facet->getUseHierarchy())) {
      $field_id = $facet->getFieldIdentifier();

      // Load the index from the source, load the definition from the
      // datasource.
      /** @var \Drupal\facets\FacetSource\SearchApiFacetSourceInterface $source */
      $index = $source->getIndex();
      $field = $index->getField($field_id);

      // Determine the target entity type.
      $entity_type = $field->getDataDefinition()
        ->getPropertyDefinition('entity')
        ->getTargetDefinition()
        ->getEntityTypeId();

      // Process taxonomy terms & remove items not included in menu.
      if ($entity_type == 'taxonomy_term') {
        $langcode = $this->languageManager->getCurrentLanguage()->getId();

        $ids = [];

        /** @var \Drupal\facets\Result\ResultInterface $result */
        foreach ($results as $delta => $result) {
          $ids[$delta] = $result->getRawValue();
        }

        // Load terms info for required term ids.
        $terms_info = $ids ? $this->getTermsInfo($ids, $langcode) : [];

        // Loop over all results.
        foreach ($results as $i => $result) {
          $term_info = isset($terms_info[$ids[$i]]) ? $terms_info[$ids[$i]] : NULL;

          if (empty($term_info) || empty($term_info->status) || empty($term_info->include)) {
            // Remove from results if either term load failed or not included
            // in menu or status is disabled.
            unset($results[$i]);
          }
        }
      }
    }

    // Return the results with the new display values.
    return $results;
  }

  /**
   * Get status and include in menu flags for the specific term ids.
   *
   * @param array $tids
   *   Term IDs.
   * @param string $langcode
   *   Language code.
   *
   * @return array
   *   DB result or empty array.
   */
  private function getTermsInfo(array $tids, string $langcode) {
    $query = $this->connection->select('taxonomy_term_field_data', 'term');
    $query->leftJoin('taxonomy_term__field_category_include_menu', 'include', 'term.tid = include.entity_id and term.langcode = include.langcode');
    $query->leftJoin('taxonomy_term__field_commerce_status', 'status', 'term.tid = status.entity_id and term.langcode = status.langcode');
    $query->addField('term', 'tid');
    $query->addField('include', 'field_category_include_menu_value', 'include');
    $query->addField('status', 'field_commerce_status_value', 'status');
    $query->condition('term.vid', 'acq_product_category');
    $query->condition('term.tid', $tids, 'IN');
    $query->condition('term.langcode', $langcode);
    $result = $query->execute()->fetchAllAssoc('tid');
    return is_array($result) ? $result : [];
  }

}
