<?php

namespace Drupal\alshaya_facets_pretty_paths\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\path_alias\AliasManagerInterface;

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
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a PathProcessorLanguage object.
   *
   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
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
    // We are doing this because alias manager is unable to get the root path.
    // We remove the facet parameters and then get the term_path.
    // For example: /en/ladies/--color-blue => en/term/10/--color-blue.
    if (stripos($path, '/--', 0) !== FALSE) {
      $path_alias = substr($path, 0, stripos($path, '/--'));
      $term_path = $this->aliasManager->getPathByAlias($path_alias);
      $query_param = substr($path, stripos($path, '/--') + 1);
      $path = $term_path . '/' . $query_param;
    }

    elseif (!empty($facet_link = $request->query->get('facet_link'))) {
      if (str_contains($facet_link, '/--')) {
        $path_alias = substr($facet_link, 0, strrpos($facet_link, '/--'));
        $term_path = $this->aliasManager->getPathByAlias($path_alias);
        $query_param = substr($facet_link, strrpos($facet_link, '/--') + 1);
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
    // For example: en/term/10/--color-blue => en/ladies/--color-blue.
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
        if (str_starts_with($path, '//')) {
          $path = '/' . ltrim($path, '/') . '/';
        }
      }
    }
    return $path;
  }

}
