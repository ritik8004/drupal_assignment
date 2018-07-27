<?php

/**
 * @file
 * Hooks specific to the alshaya_main_menu module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow other modules to modify alshaya_main_menu link items.
 *
 * @param array $term_tree
 *   Array of term tree elements.
 * @param int $parent_id
 *   Parent term id.
 * @param array $context
 *   Context containing block and term object.
 */
function hook_alshaya_main_menu_links_alter(array &$term_tree, $parent_id, array $context) {

}

/**
 * @} End of "addtogroup hooks".
 */
