<?php

/**
 * @file
 * Hooks specific to the acq_promotion module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter promotion node before it is saved during insert or update.
 *
 * Promotion data from API is passed here to allow other modules to read from
 * the data provided by API and update promotion node accordingly.
 *
 * @param \Drupal\node\NodeInterface $node
 *   Node to alter.
 * @param array $promotion
 *   Array containing details provided by API.
 * @param array $promotion_label_languages
 *   Array of promo labels for the different languages.
 */
function hook_acq_promotion_promotion_node_alter(NodeInterface $node, array $promotion, array $promotion_label_languages) {

}

/**
 * @} End of "addtogroup hooks".
 */
