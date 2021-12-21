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
   * Static paths to be used in outbound.
   *
   * @var array
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
    // We remove the color parameters and then get the pdp_path.
    // For e.g: en/buy-oversized-checked-pocket-detail-shacket/-color-khaki.html
    // => en/node/376.
    $suffix = '.html';
    if (stripos($path, '/-', 0) !== FALSE && stripos($path, $suffix, 0) !== FALSE) {
      $path_alias = substr($path, 0, stripos($path, '/-')) . $suffix;
      $pdp_path = $this->aliasManager->getPathByAlias($path_alias);
      if ($pdp_path) {
        // Keep alias and original path in static array to process
        // in outboud request.
        self::$paths[$path_alias] = $path;
        self::$paths[$pdp_path] = $path;
        $path = $pdp_path;
      }
    }

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    // For e.g: en/node/376
    // => en/buy-oversized-checked-pocket-detail-shacket/-color-khaki.html.
    if (isset(self::$paths[$path])) {
      return self::$paths[$path];
    }

    return $path;
  }

}
