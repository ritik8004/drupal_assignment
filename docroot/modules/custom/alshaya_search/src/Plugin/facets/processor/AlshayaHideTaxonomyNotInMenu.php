<?php

namespace Drupal\alshaya_search\Plugin\facets\processor;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $source = $facet->getFacetSource();

    // Support multiple entity types when using Search API.
    if ($source instanceof SearchApiDisplay) {
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

        // Load indexed term object.
        $entities = $this->entityTypeManager
          ->getStorage($entity_type)
          ->loadMultiple($ids);

        foreach ($results as $i => $result) {
          if (($entities[$ids[$i]] instanceof TermInterface) &&
            ($entities[$ids[$i]]->get('field_category_include_menu')->getString() != 1)) {
            unset($results[$i]);
          }
        }
      }
    }

    // Return the filtered results.
    return $results;
  }

}
