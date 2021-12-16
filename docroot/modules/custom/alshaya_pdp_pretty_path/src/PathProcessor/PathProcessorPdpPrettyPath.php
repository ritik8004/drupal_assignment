<?php

namespace Drupal\alshaya_pdp_pretty_path\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Path processor for alshaya_pdp_pretty_path.
 */
class PathProcessorPdpPrettyPath implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * The current_route_match service.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  protected static $paths = [];

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
    // We remove the color parameters and then get the pdp_path.
    // For example: en/buy-oveed/-color-khaki.html => en/node/376/?selected=876.
    $suffix = '.html';
    if (stripos($path, '/-', 0) !== FALSE) {
      $path_alias = substr($path, 0, stripos($path, '/-')) . $suffix;
      $pdp_path = $this->aliasManager->getPathByAlias($path_alias);
      $query_param = substr($path, stripos($path, '/-') + 1);
      self::$paths[$pdp_path] = $path;
      $path = $pdp_path;
    }

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    // For example: en/node/376/?selected=876
    // => en/buy-oversized-shoulder-pad-detail-satin-bomber/-color-khaki.html.
    if (isset(self::$paths[$path])) {
      return self::$paths[$path];
    }
    if (stripos($path, '?selected', 0) !== FALSE && empty($options['alias'])) {
      $original_path = substr($path, 0, strpos($path, '/?'));
      if ($path) {
        $suffix = '.html';
        $prefix = '/-color-black';
        $path_alias = $this->aliasManager->getAliasByPath($original_path);
        $path = str_replace($suffix, $prefix . $suffix, $path_alias);

        if (strpos($path, '//') === 0) {
          $path = '/' . ltrim($path, '/') . '/';
        }
      }
    }

    return $path;
  }

}
