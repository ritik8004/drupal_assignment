<?php

namespace Drupal\alshaya_search_api\Plugin\facets\processor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\Processor\SortProcessorPluginBase;
use Drupal\facets\Result\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acq_sku\ProductOptionsManager;

/**
 * A processor that orders the product options term-results by their weight.
 *
 * @FacetsProcessor(
 *   id = "product_options_term_weight_widget_order",
 *   label = @Translation("Product options sort by taxonomy term weight"),
 *   description = @Translation("Sorts the widget results by taxonomy term weight. This sort is only applicable for product options term-based facets."),
 *   stages = {
 *     "sort" = 60
 *   }
 * )
 */
class TermWeightWidgetOrderProductOptionsProcessor extends SortProcessorPluginBase implements ContainerFactoryPluginInterface {

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function sortResults(Result $a, Result $b) {
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $query = db_select('taxonomy_term_field_data', 'td');
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
