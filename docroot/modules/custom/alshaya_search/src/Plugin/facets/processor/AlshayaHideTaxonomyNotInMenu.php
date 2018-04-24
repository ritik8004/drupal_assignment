<?php

namespace Drupal\alshaya_search\Plugin\facets\processor;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
class AlshayaHideTaxonomyNotInMenu extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The product category tree Manager.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $categoryTreeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $category_tree_manager
   *   The Product category tree manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager, ProductCategoryTree $category_tree_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->categoryTreeManager = $category_tree_manager;
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
      $container->get('entity_type.manager'),
      $container->get('alshaya_acm_product_category.product_category_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $source = $facet->getFacetSource();

    // Support multiple entity types when using Search API.
    if (($source instanceof SearchApiDisplay) && ($facet->getUseHierarchy())) {
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
        /** @var \Drupal\facets\Result\ResultInterface $result */
        foreach ($results as $delta => $result) {
          $ids[$delta] = $result->getRawValue();
        }

        // List of term ids that need to be hidden based on its parent's not
        // included in menu flag.
        $hide_tids = [];

        if ($cached_hide_tids = \Drupal::cache()->get('alshaya_hidden_category_tids')) {
          if (!empty($cached_hide_tids->data)) {
            $hide_tids = $cached_hide_tids->data;
          }
        }

        // Process results array & mark terms for hiding & cache the list of
        // hidden terms.
        if (empty($hide_tids)) {
          // Load indexed term object.
          $entities = $this->entityTypeManager
            ->getStorage($entity_type)
            ->loadMultiple($ids);

          foreach ($results as $i => $result) {
            $term = $entities[$ids[$i]];
            if (($term instanceof TermInterface) &&
              (!$this->shouldRenderTerm($term))) {
              $hide_tids[] = $ids[$i];
            }
          }

          // Remove any duplicate term ids.
          $hide_tids = array_unique($hide_tids);

          // Cache this data to be used on other pages.
          \Drupal::cache()->set('alshaya_hidden_category_tids', $hide_tids, Cache::PERMANENT, ['acq_product_category_list']);
        }

        // Process tids marked for hiding.
        $this->hideMarkedTerms($hide_tids, $results);
      }
    }

    // Return the filtered results.
    return $results;
  }

  /**
   * Helper function to check if this term should be rendered in facet list.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Taxonomy term that is being rendered.
   *
   * @return bool
   *   Status of the Term.
   */
  protected function shouldRenderTerm(TermInterface $term) {
    if ($term->get('field_category_include_menu')->getString() != 1) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Hide Children terms that were discovered while hiding parent terms.
   *
   * @param array $hide_tids
   *   List of tids discovered.
   * @param array $results
   *   List of facet result set items.
   */
  protected function hideMarkedTerms(array $hide_tids, array &$results) {

    $keyed_results = [];

    // Results array is keyed by default index, lets index it by term ids.
    foreach ($results as $result) {
      $keyed_results[$result->getRawValue()] = $result;
    }

    // Remove child tids from results array.
    foreach ($hide_tids as $hide_tid) {
      unset($keyed_results[$hide_tid]);
    }

    // Reset array index & assign values to results array.
    $results = array_values($keyed_results);
  }

}
