<?php

namespace Drupal\alshaya_rcs_bazaar_voice\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic Bazaar Voice route events.
 */
class AlshayaRcsBazaarVoiceRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Always deny access to this route in V3 as the data now comes from MDC.
    if ($route = $collection->get('alshaya_bazaar_voice.bv_form_config')) {
      $route->setRequirement('_access', 'FALSE');
    }
  }

}
