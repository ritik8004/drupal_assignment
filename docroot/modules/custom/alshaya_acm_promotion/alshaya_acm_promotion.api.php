<?php

/**
 * @file
 * Hooks specific to the alshaya_acm_product module.
 */

use Drupal\node\NodeInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter promotion resource data.
 *
 * @param array $data
 *   Promo data array to alter.
 * @param \Drupal\node\NodeInterface $node
 *   Promo node object.
 *
 * @see \Drupal\alshaya_acm_promotion\Plugin\rest\resource\PromotionsResource
 */
function hook_alshaya_acm_promo_resource_alter(array &$data, NodeInterface $node) {
  $data['title'] = 'Test Promo';
}

/**
 * @} End of "addtogroup hooks".
 */
