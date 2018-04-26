<?php

/**
 * @file
 * Hooks for the alshaya super category module.
 */

/**
 * Hook to react on super category status change.
 *
 * @param bool $status
 *   The super category status.
 * @param int $default_parent
 *   The default super category term id.
 * @param bool $path_alter
 *   The path alter status based on super category status.
 */
function hook_alshaya_super_category_status_update($status, $default_parent, $path_alter) {
  if ($status) {
    $config = \Drupal::configFactory()->getEditable('search_api.index.acquia_search_index');
    // Index attr concept data in search_api_db.
    $config->set('field_settings.field_category_parent', []);
    $config->save();
  }
}
