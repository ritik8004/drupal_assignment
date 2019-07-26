<?php

/**
 * @file
 * Hooks specific to the alshaya_social module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter network info.
 *
 * Alter the social auth network info, where SOCIAL_NETWORK is, machine name of
 * social auth module. i.e. social_auth_facebook, social_auth_google etc..
 *
 * @param array $network_info
 *   Array of current network info.
 * @param string $route_name
 *   Current route name.
 */
function hook_alshaya_social_SOICAL_NETWORK_alter(array &$network_info, $route_name) {

}

/**
 * @} End of "addtogroup hooks".
 */
