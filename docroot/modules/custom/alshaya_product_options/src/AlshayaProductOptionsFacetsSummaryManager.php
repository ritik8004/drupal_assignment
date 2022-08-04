<?php

namespace Drupal\alshaya_product_options;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Link;
use Drupal\facets\Entity\Facet;
use Drupal\facets\Exception\InvalidProcessorException;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\facets\FacetSource\FacetSourcePluginManager;
use Drupal\facets_summary\FacetsSummaryInterface;
use Drupal\facets_summary\FacetsSummaryManager\DefaultFacetsSummaryManager;
use Drupal\facets_summary\Processor\BuildProcessorInterface;
use Drupal\facets_summary\Processor\ProcessorInterface;
use Drupal\facets_summary\Processor\ProcessorPluginManager;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class Alshaya Product Options Facets Summary Manager.
 *
 * @package Drupal\alshaya_product_options
 */
class AlshayaProductOptionsFacetsSummaryManager extends DefaultFacetsSummaryManager {

  /**
   * Facet Summary Manager.
   *
   * @var \Drupal\facets_summary\FacetsSummaryManager\DefaultFacetsSummaryManager
   */
  protected $facetSummaryManager;

  /**
   * Swatches Helper service.
   *
   * @var \Drupal\alshaya_product_options\SwatchesHelper
   */
  protected $swatches;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new instance of the AlshayaProductOptionsFacetsSummaryManager.
   *
   * @param \Drupal\facets_summary\FacetsSummaryManager\DefaultFacetsSummaryManager $facet_summary_manager
   *   Facet Summary Manager.
   * @param \Drupal\alshaya_product_options\SwatchesHelper $swatches
   *   Swatches Helper service.
   * @param \Drupal\facets\FacetSource\FacetSourcePluginManager $facet_source_manager
   *   The facet source plugin manager.
   * @param \Drupal\facets_summary\Processor\ProcessorPluginManager $processor_plugin_manager
   *   The facets summary processor plugin manager.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   The facet manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(DefaultFacetsSummaryManager $facet_summary_manager,
                              SwatchesHelper $swatches,
                              FacetSourcePluginManager $facet_source_manager,
                              ProcessorPluginManager $processor_plugin_manager,
                              DefaultFacetManager $facet_manager,
                              ConfigFactoryInterface $config_factory) {
    $this->facetSummaryManager = $facet_summary_manager;
    $this->swatches = $swatches;
    $this->configFactory = $config_factory;

    parent::__construct($facet_source_manager, $processor_plugin_manager, $facet_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetsSummaryInterface $facets_summary) {
    // Let the facet_manager build the facets.
    $facetsource_id = $facets_summary->getFacetSourceId();

    static $static = NULL;
    if (isset($static[$facetsource_id])) {
      return $static[$facetsource_id];
    }

    /** @var \Drupal\facets\Entity\Facet[] $facets */
    $facets = $this->facetManager->getFacetsByFacetSourceId($facetsource_id);
    // Get the current results from the facets and let all processors that
    // trigger on the build step do their build processing.
    // @see \Drupal\facets\Processor\BuildProcessorInterface.
    // @see \Drupal\facets\Processor\SortProcessorInterface.
    $this->facetManager->updateResults($facetsource_id);

    $facets_config = $facets_summary->getFacets();
    // Exclude facets which were not selected for this summary.
    $facets = array_filter($facets,
      fn($item) => isset($facets_config[$item->id()])
    );

    foreach ($facets as $facet) {
      // For clarity, process facets is called each build.
      // The first facet therefor will trigger the processing. Note that
      // processing is done only once, so repeatedly calling this method will
      // not trigger the processing more than once.
      $this->facetManager->build($facet);
    }

    $build = [
      '#theme' => 'facets_summary_item_list',
      '#attributes' => [
        'data-drupal-facets-summary-id' => $facets_summary->id(),
      ],
    ];

    $results = [];
    foreach ($facets as $facet) {
      $show_count = $facets_config[$facet->id()]['show_count'];
      $results = array_merge($results, $this->buildResultTree($show_count, $facet->getResults(), $facet));
    }
    $build['#items'] = $results;

    // Allow our Facets Summary processors to alter the build array in a
    // configured order.
    foreach ($facets_summary->getProcessorsByStage(ProcessorInterface::STAGE_BUILD) as $processor) {
      if (!$processor instanceof BuildProcessorInterface) {
        throw new InvalidProcessorException("The processor {$processor->getPluginDefinition()['id']} has a build definition but doesn't implement the required BuildProcessorInterface interface");
      }
      $build = $processor->build($facets_summary, $build, $facets);
    }

    $static[$facetsource_id] = $build;
    return $build;
  }

  /**
   * Build result tree, taking possible children into account.
   *
   * @param bool $show_count
   *   Show the count next to the facet.
   * @param \Drupal\facets\Result\ResultInterface[] $results
   *   Facet results array.
   * @param \Drupal\facets\Entity\Facet $facet
   *   Facet.
   *
   * @return array
   *   The rendered links to the active facets.
   */
  protected function buildResultTree($show_count, array $results, Facet $facet = NULL) {
    $items = [];
    foreach ($results as $result) {
      if ($result->isActive()) {
        $swatch = $this->swatches->getSwatchForFacet($facet, $result->getDisplayValue());
        $value = $swatch ? $swatch['name'] : $result->getDisplayValue();

        // Change the size label if size grouping is enabled.
        if ($facet->getFieldIdentifier() == 'attr_size'
          && str_contains($value, SkuManager::SIZE_GROUP_SEPARATOR)
          && $this->isSizeGroupEnabled()) {
          $sizeGroupArr = explode(SkuManager::SIZE_GROUP_SEPARATOR, $value);
          $value = $sizeGroupArr[1];
        }
        $item = [
          '#theme' => 'facets_result_item__summary',
          '#value' => $value,
          '#show_count' => $show_count,
          '#count' => $result->getCount(),
          '#is_active' => TRUE,
        ];
        $item = (new Link($item, $result->getUrl()))->toRenderable();
        $item['#attributes']['data-drupal-facet-id'] = $facet->id();
        $item['#attributes']['data-drupal-facet-item-value'] = $result->getRawValue();
        $items[] = $item;
      }
      if ($children = $result->getChildren()) {
        $items = array_merge($items, $this->buildResultTree($show_count, $children, $facet));
      }
    }
    return $items;
  }

  /**
   * Check if size grouping filter is enabled.
   *
   * @return int
   *   0 if not available, 1 if size grouping available.
   */
  protected function isSizeGroupEnabled() {
    static $status = NULL;

    if (!isset($status)) {
      $status = $this->configFactory->get('alshaya_acm_product.settings')->get('enable_size_grouping_filter');
    }

    return $status;
  }

}
