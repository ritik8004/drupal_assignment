<?php

/**
 * @file
 * Hooks specific to the alshaya_knet module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the success route for K-Net payment.
 *
 * @param string $route
 *   Route name.
 * @param array $state_data
 *   State data.
 *
 * @see \Drupal\alshaya_knet\Controller\KnetController::response()
 */
function hook_alshaya_knet_success_route_alter(string &$route, array &$state_data) {
  $route = 'test_route.settings';
}

/**
 * @} End of "addtogroup hooks".
 */
