<?php

/**
 * @file
 * Post update functions for alshaya_search.
 */

/**
 * Implements hook_post_update_NAME().
 *
 * Re-save index to create required tables.
 *
 * We do this in post update hook as it doesn't work together with config update
 * in normal hook_update.
 */
function alshaya_search_post_update_8012(&$sandbox) {
  _alshaya_search_resave_indexes();
}
