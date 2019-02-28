<?php

namespace Drupal\alshaya_product\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Removes other terms (except child terms of current term) from facet items.
 *
 * @FacetsProcessor(
 *   id = "alshaya_remove_current_term",
 *   label = @Translation("Alshaya remove the current term"),
 *   description = @Translation("Removes the current term from the facet items (Only for PLP)."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class AlshayaRemoveCurrentTermProcessor extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaRemoveCurrentTermProcessor constructor.
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
    if (!empty($results)) {
      // Get current page term.
      $current_term = $this->getCurrentPageTerm();

      // If no term, means no PLP page. So we not process further.
      if (empty($current_term)) {
        return $results;
      }

      // Children of current term.
      $children = $facet->getHierarchyInstance()->getNestedChildIds($current_term);

      foreach ($results as $key => $result) {
        // If term is not the child of current term.
        if (!in_array($result->getRawValue(), $children)) {
          unset($results[$key]);
        }
      }
    }

    // Return the results.
    return $results;
  }

  /**
   * Get the current page term id.
   *
   * @return int|null
   *   Current term id.
   */
  protected function getCurrentPageTerm() {
    if ($this->currentRequest->attributes->get('_route') == 'entity.taxonomy_term.canonical') {
      $term = $this->currentRequest->attributes->get('taxonomy_term');
      if ($term instanceof TermInterface) {
        return $term->id();
      }
    }
    elseif ($this->currentRequest->attributes->get('_route') == 'rest.category_product_list.GET') {
      // In case of rest resource.
      return $this->currentRequest->attributes->get('id');
    }

    return NULL;
  }

}
