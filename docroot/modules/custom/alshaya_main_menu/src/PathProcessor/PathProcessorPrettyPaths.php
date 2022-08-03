<?php

namespace Drupal\alshaya_main_menu\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Path processor for view-all.html category pretty path.
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
   * {@inheritdoc}
   */
  public static $isRequestViewAll = FALSE;

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
    if (stripos($path, '/view-all', 0) !== FALSE) {
      $path_alias = substr($path, 0, stripos($path, '/view-all'));
      $term_path = $this->aliasManager->getPathByAlias($path_alias);
      $query_param = substr($path, stripos($path, '/view-all') + 1);
      $path = $term_path . '/' . $query_param;
      self::$isRequestViewAll = TRUE;
    }
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    // For example: en/term/10/--color-blue => en/ladies/--color-blue.
    if (stripos($path, '/view-all', 0) !== FALSE && empty($options['alias'])) {
      $original_path = substr($path, 0, strpos($path, '/view-all'));
      if ($original_path) {
        $path_alias = $this->aliasManager->getAliasByPath($original_path);
        $query_param = substr($path, strpos($path, "/view-all") + 1);
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
