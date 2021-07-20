<?php

/**
 * @file
 * Hooks definition specific to the rcs_placeholders module.
 */

/**
 * Allows other modules to alter the list of placeholder attributes.
 *
 * Only these attributes will be scanned during replacement.
 *
 * @param array $attributes
 *   The array of attributes.
 */
function rcs_placeholders_placeholder_attributes_alter(array $attributes) {
  $attributes[] = 'data-xyz';
}
