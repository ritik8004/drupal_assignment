<?php

namespace Drupal\alshaya_facets_pretty_paths;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\Core\Path\AliasManagerInterface;

/**
 * Path processor for alshaya_facets_pretty_paths.
 */
class PathProcessorPrettyPaths implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * The current_route_match service.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a PathProcessorLanguage object.
   *
   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(ResettableStackedRouteMatchInterface $route_match, AliasManagerInterface $alias_manager) {
    $this->routeMatch = $route_match;
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    if ($this->routeMatch->getRouteName() == 'facets.block.ajax' || stripos($path, '/--', 0) !== FALSE) {
      if (strrpos($path, '/--')) {
        $path_alias = substr($path, 0, strrpos($path, '/--'));
        $term_path = $this->aliasManager->getPathByAlias($path_alias);
        $query_param = substr($path, strrpos($path, '/--') + 1);
        $path = $term_path . '/' . $query_param;
      }
    }

    elseif (!empty($facet_link = $request->query->get('facet_link'))) {
      if (!strpos($facet_link, '/--')) {
        $path_alias = substr($facet_link, 0, strrpos($facet_link, '/--'));
        $term_path = $this->aliasManager->getPathByAlias($path_alias);
        $query_param = substr($path, strrpos($path, '/--') + 1);
        $facet_link = $term_path . '/' . $query_param;
        $request->query->set('facet_link', $facet_link);
      }
    }

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    if (stripos($path, '/--', 0) !== FALSE && empty($options['alias'])) {
      $original_path = substr($path, 0, strpos($path, '/--'));
      if ($original_path) {
        $path_alias = $this->aliasManager->getAliasByPath($original_path);
        $query_param = substr($path, strpos($path, "/--") + 1);
        $path = $path_alias . '/' . $query_param;
        // Ensure the resulting path has at most one leading slash,to prevent it
        // becoming an external URL without a protocol like //example.com. This
        // is done in \Drupal\Core\Routing\UrlGenerator::generateFromRoute()
        // to protect against this problem in arbitrary path processors,
        // but it is duplicated here to protect any other URL generation code
        // that might call this method separately.
        if (strpos($path, '//') === 0) {
          $path = '/' . ltrim($path, '/');
        }
      }
    }
    return $path;
  }

}
