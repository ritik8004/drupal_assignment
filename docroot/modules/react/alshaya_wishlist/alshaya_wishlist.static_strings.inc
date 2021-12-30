<?php

/**
 * @file
 * Contains wishlist related static strings and translations.
 */

/**
 * Wishlist strings.
 */
function _alshaya_wishlist_strings() {
  return [
    [
      'key' => 'add_to_wishlist',
      'value' => t('Add to @wishlist_label', [], ['context' => 'wishlist']),
    ],
    [
      'key' => 'added_to_wishlist',
      'value' => t('Added to @wishlist_label', [], ['context' => 'wishlist']),
    ],
    [
      'key' => 'move_to_wishlist',
      'value' => t('Move to @wishlist_label', [], ['context' => 'wishlist']),
    ],
    [
      'key' => 'remove_from_wishlist',
      'value' => t('Remove', [], ['context' => 'wishlist']),
    ],
  ];
}
