<?php

namespace Drupal\alshaya_acm_checkout\Routing;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change title_callback for checkout mulisteps pages.
    if ($route = $collection->get('acq_checkout.form')) {
      $route->setDefault('_title_callback', '\Drupal\alshaya_acm_checkout\Routing\RouteSubscriber::checkoutPageTitle');
    }
  }

  /**
   * Page title for checkout steps page.
   */
  public function checkoutPageTitle(RouteMatchInterface $route_match) {
    // Current checkout step.
    $current_step = $route_match->getParameter('step');
    // Get the list of all available checkout steps.
    $config = \Drupal::config('acq_checkout.settings');
    $checkoutFlowPlugin = $config->get('checkout_flow_plugin') ?: 'multistep_default';
    $plugin_manager = \Drupal::service('plugin.manager.acq_checkout_flow');
    $type = $plugin_manager->createInstance($checkoutFlowPlugin, ['validate_current_step' => TRUE]);
    $steps = $type->getVisibleSteps();
    // Get the title of the current checkout step.
    if (!empty($steps[$current_step]) && !empty($steps[$current_step]['title'])) {
      return $steps[$current_step]['title'];
    }
    return t('Checkout');
  }

}
