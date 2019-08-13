<?php

namespace Drupal\alshaya_search_api\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Show only the direct child terms for a term in category facet.
 *
 * @FacetsProcessor(
 *   id = "alshaya_show_direct_child_terms",
 *   label = @Translation("Alshaya show direct child terms"),
 *   description = @Translation("Show only the direct child terms of a term (Only for PLP)."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class AlshayaShowDirectChildTerms extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaShowDirectChildTerms constructor.
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

      // Get all direct child terms of current term.
      $query = \Drupal::database()->select('taxonomy_term__parent', 'ttp');
      $query->fields('ttp', ['entity_id']);
      $query->condition('ttp.parent_target_id', $current_term);
      $direct_child_terms = $query->execute()->fetchCol();

      if (!empty($direct_child_terms)) {
        foreach ($results as $key => $result) {
          $tid = $result->getRawValue();
          // If term in facet result not a direct child, remove/unset it.
          if (!in_array($tid, $direct_child_terms)) {
            unset($results[$key]);
          }
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

    return NULL;
  }

}
