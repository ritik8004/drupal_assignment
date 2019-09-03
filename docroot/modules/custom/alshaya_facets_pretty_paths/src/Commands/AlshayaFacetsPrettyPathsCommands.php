<?php

namespace Drupal\alshaya_facets_pretty_paths\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\facets_summary\Entity\FacetsSummary;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaFacetsPrettyPathsCommands.
 *
 * @package Drupal\alshaya_facets_pretty_paths\Commands
 */
class AlshayaFacetsPrettyPathsCommands extends DrushCommands {

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
   * AlshayaFacetsPrettyPathsCommands constructor.
   *
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facets_manager
   *   Facet manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(DefaultFacetManager $facets_manager,
                              ConfigFactoryInterface $config_factory) {
    $this->facetManager = $facets_manager;
    $this->configFactory = $config_factory;
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
  public function prettyPathsToggle(array $options = ['action' => NULL, 'page' => NULL]) {
    if ($options['action'] == 'en' && $options['page']) {
      $this->enablePrettyPaths($options['page']);
      $this->output()->writeln('Successfully enabled alshaya_facets_pretty_paths on ' . $options['page']);
    }
    elseif ($options['action'] == 'dis' && $options['page']) {
      $this->disablePrettyPaths($options['page']);
      $this->output()->writeln('Successfully disabled alshaya_facets_pretty_paths on ' . $options['page']);
    }
    else {
      $this->output()->writeln('Please specify action (en/dis) and page(plp/promo/search).');
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

    // The url processor for facet_source and reset_facets processor
    // for facets_summary both need to be changed for pretty facets to work.
    // Set the url processor as alshaya_pretty_paths for PLP.
    $this->configFactory
      ->getEditable('facets.facet_source.search_api__' . $mapping['id'])
      ->set('url_processor', 'alshaya_facets_pretty_paths')
      ->save();

    // Set url alias for facets.
    foreach ($facets as $facet) {
      if ($facet->getFacetSourceId() == 'search_api:' . $mapping['id']) {
        // Get the current pretty alias if already set or available in mapping.
        $alias = ($facet->getThirdPartySetting('alshaya_facets_pretty_paths', 'pretty_url_alias') ??
          $mapping['alias'][$facet->id()]) ??
          strtolower(str_replace(' ', '_', $facet->get('name')));
        $facet->setThirdPartySetting('alshaya_facets_pretty_paths', 'pretty_url_alias', $alias);
        $facet->setThirdPartySetting('alshaya_facets_pretty_paths', 'url_alias', $facet->getUrlAlias());
        $alias = $mapping['alias'][$facet->id()] ?? strtolower(str_replace(' ', '_', $facet->get('name')));
        $facet->setUrlAlias($alias);
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
    $mappings = _alshaya_facets_pretty_paths_get_mappings();

    /* @var \Drupal\facets\FacetInterface[] $facets */
    $facets = $this->facetManager->getEnabledFacets();

    foreach ($mappings as $mapping) {
      $source = $this->configFactory
        ->getEditable('facets.facet_source.search_api__' . $mapping['id']);
      if ($source->get('url_processor') != 'alshaya_facets_pretty_paths') {
        continue;
      }

      // Revert url processor.
      $source->set('url_processor', 'query_string');
      $source->save();

      // Revert url alias for facets.
      foreach ($facets as $facet) {
        if ($facet->getFacetSourceId() == 'search_api:' . $mapping['id']) {
          $alias = $facet->getThirdPartySetting('alshaya_facets_pretty_paths', 'url_alias', $facet->getUrlAlias());
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
    }
  }

}
