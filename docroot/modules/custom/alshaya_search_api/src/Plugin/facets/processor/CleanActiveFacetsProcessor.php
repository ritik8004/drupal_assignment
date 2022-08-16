<?php

namespace Drupal\alshaya_search_api\Plugin\facets\processor;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Clean all active facets based on current result.
 *
 * @FacetsProcessor(
 *   id = "clean_active_facets_processor",
 *   label = @Translation("Clean Active Facets processor"),
 *   description = @Translation("Clean the active facets based on new result."),
 *   stages = {
 *     "build" = 10,
 *   },
 *   locked = true
 * )
 */
class CleanActiveFacetsProcessor extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * CleanActiveFacetsProcessor constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $alias = $facet->getUrlAlias();
    $params = $this->getFacetParams($alias);

    if (empty($params)) {
      return $results;
    }

    // Result items in facet.
    $facet_results = [];
    foreach ($facet->getResults() as $result) {
      $facet_results[] = $result->getRawValue();
    }

    // Active facet result items.
    $facet_active_items = $facet->getActiveItems();

    // If we have a facet value as active but that is not available in facet
    // results, then remove/unset it.
    foreach ($facet_active_items as $index => $facet_active_item) {
      if (!in_array($facet_active_item, $facet_results)) {
        $this->removeFacetParam($alias . ':' . $facet_active_item);
        unset($facet_active_items[$index]);
      }
    }

    $facet->setActiveItems($facet_active_items);

    return $results;
  }

  /**
   * Get params for current facet.
   *
   * @param string $alias
   *   Facet alias.
   *
   * @return array
   *   Empty array if not filtered by current facet.
   */
  private function getFacetParams(string $alias): array {
    $params = [];
    $query = $this->currentRequest->query->get('f', []);

    foreach ($query as $q) {
      if (str_starts_with($q, $alias)) {
        $params[] = $q;
      }
    }

    return $params;
  }

  /**
   * Remove value from facets query string ($_GET).
   *
   * @param string $value
   *   Value to remove.
   */
  private function removeFacetParam(string $value): void {
    $query = $this->currentRequest->query->get('f', []);

    foreach ($query as $index => $q) {
      if ($q == $value) {
        unset($query[$index]);
      }
    }

    $query = array_values($query);

    $this->currentRequest->query->get('f', $query);
  }

}
