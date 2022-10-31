<?php

/**
 * @file
 * Post update functions for alshaya_acm_product.
 */

/**
 * Implements hook_post_update_NAME().
 *
 * Re-save index to create required tables.
 */
function alshaya_acm_product_post_update_8030(&$sandbox) {
  // This is required because with deleting and re-adding the attribute,
  // also deletes/removes the attribute info from search api.
  _alshaya_search_resave_indexes();
}

/**
 * Implements hook_post_update_NAME().
 *
 * Re-save index to create required tables.
 *
 * We do this in post update hook as it doesn't work together with config update
 * in normal hook_update.
 */
function alshaya_acm_product_post_update_8026(&$sandbox) {
  _alshaya_search_resave_indexes();
}

/**
 * Implements hook_post_update_NAME().
 *
 * Re-save index to create required tables.
 *
 * We do this in post update hook as it doesn't work together with config update
 * in normal hook_update.
 */
function alshaya_acm_product_post_update_8017(&$sandbox) {
  _alshaya_search_resave_indexes();
}
