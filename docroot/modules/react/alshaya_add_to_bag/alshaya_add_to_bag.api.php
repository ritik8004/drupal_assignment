<?php

/**
 * @file
 * Hooks specific to the alshaya_add_to_bag module.
 */

/**
 * Allow other modules to alter the feature status.
 *
 * Anywhere, if you want to override the add to bag feature status. For ex
 * we can override the add to bag feature status for using this with
 * wishlist page.
 *
 * @param bool $add_to_bag_feature_status
 *   Boolean flag with feature status.
 */
function hook_alshaya_add_to_bag_feature_status_alter(&$add_to_bag_feature_status) {

}
