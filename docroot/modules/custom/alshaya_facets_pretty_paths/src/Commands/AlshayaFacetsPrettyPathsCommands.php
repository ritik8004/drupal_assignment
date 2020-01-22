<?php

namespace Drupal\alshaya_facets_pretty_paths\Commands;

use Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\facets_summary\Entity\FacetsSummary;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaFacetsPrettyPathsCommands.
 *
 * @package Drupal\alshaya_facets_pretty_paths\Commands
 */
class AlshayaFacetsPrettyPathsCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The router builder.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * AlshayaFacetsPrettyPathsCommands constructor.
   *
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facets_manager
   *   Facet manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder service.
   */
  public function __construct(DefaultFacetManager $facets_manager,
                              ConfigFactoryInterface $config_factory,
                              RouteBuilderInterface $router_builder) {
    $this->facetManager = $facets_manager;
    $this->configFactory = $config_factory;
    $this->routerBuilder = $router_builder;
  }

  /**
   * Enable/Disable pretty paths.
   *
   * @param array $options
   *   Command options.
   *
   * @command alshaya_facets_pretty_paths:pretty-paths-toggle
   *
   * @option action Enable or Disable.
   * @option page The page where pretty paths needs to be enabled or
   *   disabled - plp/promo/search.
   *
   * @aliases pretty-paths-toggle
   *
   * @usage drush pretty-paths-toggle --action=en --page=plp
   *   Enable facets pretty paths on plp.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function prettyPathsToggle(array $options = [
    'action' => NULL,
    'page' => NULL,
  ]) {
    if ($options['action'] == 'en' && $options['page']) {
      $this->enablePrettyPaths($options['page']);
    }
    elseif ($options['action'] == 'dis' && $options['page']) {
      $this->disablePrettyPaths($options['page']);
    }
    else {
      $this->output()
        ->writeln('Please specify action (en/dis) and page(plp/promo/search).');
    }

  }

  /**
   * Wrapper function to enable pretty path for specific listing page type.
   *
   * @param string $type
   *   Page type - plp / promo / search.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function enablePrettyPaths(string $type) {
    $mapping = _alshaya_facets_pretty_paths_get_mappings()[$type];

    /* @var \Drupal\facets\FacetInterface[] $facets */
    $facets = $this->facetManager->getEnabledFacets();

    $source = $this->configFactory->getEditable('facets.facet_source.search_api__' . $mapping['id']);

    if ($source->get('url_processor') == 'alshaya_facets_pretty_paths') {
      $this->output()->writeln('alshaya_facets_pretty_paths already enabled on ' . $type);
      return;
    }

    // The url processor for facet_source and reset_facets processor
    // for facets_summary both need to be changed for pretty facets to work.
    // Set the url processor as alshaya_pretty_paths for PLP.
    $source->set('url_processor', 'alshaya_facets_pretty_paths')->save();

    // Set url alias and meta info type for facets.
    foreach ($facets as $facet) {
      if ($facet->getFacetSourceId() == 'search_api:' . $mapping['id']) {
        $facet->setThirdPartySetting('alshaya_facets_pretty_paths', 'url_alias', $facet->getUrlAlias());
        $alias = $mapping['alias'][$facet->id()] ?? strtolower(str_replace(' ', '_', $facet->get('name')));
        $facet->setUrlAlias($alias);
        $meta_info_type = [
          'type' => AlshayaFacetsPrettyPathsHelper::FACET_META_TYPE_PREFIX,
          'prefix_text' => '',
          'visibility' => [
            AlshayaFacetsPrettyPathsHelper::VISIBLE_IN_PAGE_TITLE,
            AlshayaFacetsPrettyPathsHelper::VISIBLE_IN_META_TITLE,
            AlshayaFacetsPrettyPathsHelper::VISIBLE_IN_META_DESCRIPTION,
          ],
        ];
        if (strpos($facet->id(), 'price') > -1) {
          $meta_info_type['type'] = AlshayaFacetsPrettyPathsHelper::FACET_META_TYPE_SUFFIX;
          $meta_info_type['prefix_text'] = $this->t('at');
          $meta_info_type['visibility'] = [AlshayaFacetsPrettyPathsHelper::VISIBLE_IN_META_DESCRIPTION];
        }
        elseif (strpos($facet->id(), 'size') > -1) {
          $meta_info_type['prefix_text'] = $this->t('Size');
        }
        $facet->setThirdPartySetting('alshaya_facets_pretty_paths', 'meta_info_type', $meta_info_type);
        $facet->save();
      }
    }

    // Set the facets_summary processor as alshaya_reset_facets for PLP.
    $summary = FacetsSummary::load($mapping['summary']);

    if ($summary instanceof FacetsSummary) {
      $processor_configs = $summary->getProcessorConfigs();
      if (isset($processor_configs['reset_facets'])) {
        $processor_configs['reset_facets']['processor_id'] = 'alshaya_reset_facets';
        $summary->addProcessor($processor_configs['reset_facets']);
        $summary->removeProcessor('reset_facets');
        $summary->save();
      }
    }

    // Rebuild routes.
    $this->routerBuilder->rebuild();

    $this->output()->writeln('Successfully enabled alshaya_facets_pretty_paths on ' . $type);
  }

  /**
   * Wrapper function to disable pretty path for specific listing page type.
   *
   * @param string $type
   *   Page type - plp / promo / search.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function disablePrettyPaths(string $type) {
    $mapping = _alshaya_facets_pretty_paths_get_mappings()[$type];

    /* @var \Drupal\facets\FacetInterface[] $facets */
    $facets = $this->facetManager->getEnabledFacets();

    $source = $this->configFactory->getEditable('facets.facet_source.search_api__' . $mapping['id']);
    if ($source->get('url_processor') != 'alshaya_facets_pretty_paths') {
      $this->output()->writeln('Could not disable, alshaya_facets_pretty_paths is not enabled on ' . $type);
      return;
    }

    // Revert url processor.
    $source->set('url_processor', 'query_string');
    $source->save();

    // Revert url alias for facets.
    foreach ($facets as $facet) {
      if ($facet->getFacetSourceId() == 'search_api:' . $mapping['id']) {
        $alias = $facet->getThirdPartySetting('alshaya_facets_pretty_paths', 'url_alias', $facet->id());
        $facet->setUrlAlias($alias);
        $facet->save();
      }
    }

    // Set the facets_summary processor back to reset_facets.
    $summary = FacetsSummary::load($mapping['summary']);
    if ($summary instanceof FacetsSummary) {
      $processor_configs = $summary->getProcessorConfigs();
      if (isset($processor_configs['alshaya_reset_facets'])) {
        $processor_configs['alshaya_reset_facets']['processor_id'] = 'reset_facets';
        $summary->addProcessor($processor_configs['alshaya_reset_facets']);
        $summary->removeProcessor('alshaya_reset_facets');
        $summary->save();
      }
    }

    // Rebuild routes.
    $this->routerBuilder->rebuild();

    $this->output()->writeln('Successfully disabled alshaya_facets_pretty_paths on ' . $type);
  }

}
