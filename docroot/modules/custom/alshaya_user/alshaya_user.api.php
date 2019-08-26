<?php

/**
 * @file
 * Hooks specific to the alshaya_user module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow other modules to add/remove my account links.
 *
 * @param array $links
 *   My Account links.
 */
function hook_alshaya_my_account_links_alter(array &$links) {

}

/**
 * Allow other modules to add/remove my account routes for breadcrumb.
 *
 * @param array $routes
 *   My Account routes.
 */
function hook_alshaya_user_breadcrumb_routes_alter(array &$routes) {

}

/**
 * @} End of "addtogroup hooks".
 */
