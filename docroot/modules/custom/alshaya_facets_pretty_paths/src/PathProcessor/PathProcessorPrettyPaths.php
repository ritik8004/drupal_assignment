<?php

namespace Drupal\alshaya_facets_pretty_paths\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

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
   * Flag to check size grouping filter enbled/disabled.
   *
   * @var sizeGroupingEnabled
   */
  protected $sizeGroupingEnabled;

  /**
   * Constructs a PathProcessorLanguage object.
   *
   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(ResettableStackedRouteMatchInterface $route_match, AliasManagerInterface $alias_manager, ConfigFactoryInterface $config_factory) {
    $this->routeMatch = $route_match;
    $this->aliasManager = $alias_manager;
    $this->sizeGroupingEnabled = $config_factory->get('alshaya_acm_product.settings')->get('enable_size_grouping_filter');
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    // We are doing this because alias manager is unable to get the root path.
    // We remove the facet parameters and then get the term_path.
    // For example: /en/ladies/--color-blue => en/term/10/--color-blue.
    if (stripos($path, '/--', 0) !== FALSE) {
      $path_alias = substr($path, 0, strrpos($path, '/--'));
      $term_path = $this->aliasManager->getPathByAlias($path_alias);
      $query_param = substr($path, strrpos($path, '/--') + 1);
      // We have grouping case for size filter.
      // Here we are updating size filter pattern with grouping
      // for inbound url for getting correct data as per indexing value.
      if ($this->sizeGroupingEnabled && strpos($query_param, '--size', 0) !== FALSE) {
        $extractQueryStrings = explode('--', $query_param);
        foreach ($extractQueryStrings as $index => $extractQueryString) {
          if ($extractQueryString && strpos($extractQueryString, 'size') !== FALSE && strpos($extractQueryString, ':') !== FALSE) {
            $array = explode('-', $extractQueryString);
            $key = array_shift($array);
            foreach ($array as $sizeFilter) {
              $selectedSizeFilter = explode(':', $sizeFilter);
              $sizeFilterArr[] = 'sizegroup|' . $selectedSizeFilter[0] . '||size|' . $selectedSizeFilter[1];
            }
            $query_param_arr[$index] = $key . '-' . implode('-', $sizeFilterArr);
          }
          else {
            $query_param_arr[$index] = $extractQueryString;
          }

        }

        $query_param = implode('--', $query_param_arr);
      }

      $path = $term_path . '/' . $query_param;
    }

    elseif (!empty($facet_link = $request->query->get('facet_link'))) {
      if (strpos($facet_link, '/--') !== FALSE) {
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
        // We have grouping case for size filter.
        // Here we are updating size filter pattern with grouping
        // for outbound url for for making pretty paths for size filters.
        if ($this->sizeGroupingEnabled && strpos($query_param, '--size') !== FALSE) {
          $query_param = str_replace(
              ['sizegroup|', '||size|', '||'],
              ['', '||', ':'],
              strtolower($query_param)
            );
        }

        $path = $path_alias . '/' . $query_param;
        // Ensure the resulting path has at most one leading slash,to prevent it
        // becoming an external URL without a protocol like //example.com. This
        // is done in \Drupal\Core\Routing\UrlGenerator::generateFromRoute()
        // to protect against this problem in arbitrary path processors,
        // but it is duplicated here to protect any other URL generation code
        // that might call this method separately.
        if (strpos($path, '//') === 0) {
          $path = '/' . ltrim($path, '/') . '/';
        }
      }
    }
    return $path;
  }

}
