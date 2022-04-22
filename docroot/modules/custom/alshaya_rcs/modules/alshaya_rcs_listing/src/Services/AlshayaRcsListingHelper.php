<?php

namespace Drupal\alshaya_rcs_listing\Services;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;

/**
 * Contains helper methods rcs listing.
 */
class AlshayaRcsListingHelper {

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
   * Returns the URL key for the listing for use in graphql requests.
   *
   * @return string
   *   The listing page url key.
   */
  public function getListingUrlKey() {
    $url_key = RcsPhPathProcessor::getFullPath(TRUE);
    return str_replace('.html', '', $url_key);
  }

}
