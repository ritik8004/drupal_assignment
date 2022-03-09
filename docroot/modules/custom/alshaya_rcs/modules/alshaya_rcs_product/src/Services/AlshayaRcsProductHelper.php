<?php

namespace Drupal\alshaya_rcs_product\Services;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;

/**
 * Contains helper methods rcs product.
 */
class AlshayaRcsProductHelper {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   Config factory.
   */
  public function __construct(
    RouteMatchInterface $current_route_match
  ) {
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * Returns the URL key for the product for use in graphql requests.
   *
   * @return string
   *   The product url key.
   */
  public function getProductUrlKey() {
    $url_key = RcsPhPathProcessor::getFullPath(TRUE);
    return str_replace('.html', '', $url_key);
  }

  /**
   * Returns if ppage is RCS PDP or not.
   *
   * @return bool
   *   If page is RCS PDP or not.
   */
  public function isRcsPdp() {
    foreach ($this->currentRouteMatch->getParameters() as $route_parameter) {
      if ($route_parameter instanceof NodeInterface) {
        if ($route_parameter->bundle() === 'rcs_product') {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
