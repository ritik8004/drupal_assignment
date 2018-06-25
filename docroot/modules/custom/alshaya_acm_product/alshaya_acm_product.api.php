<?php

/**
 * @file
 * Hooks specific to the alshaya_acm_product module.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter product build to match brand needs.
 *
 * @param array $build
 *   Build array to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   User object.
 * @param string $context
 *   Context - pdp/search/modal/teaser.
 */
function hook_alshaya_acm_product_build_alter(array &$build, SKUInterface $sku, $context = 'pdp') {

}

/**
 * Alter ajax response on ajax cart render.
 *
 * @param \Drupal\Core\Ajax\AjaxResponse $response
 *   Add response command to react on stock status or for ajax cart render.
 * @param object $entity
 *   The node for which ajax cart is being rendered.
 * @param int $stock
 *   The stock status of current product.
 */
function hook_alshaya_acm_product_ajax_cart_form_alter(AjaxResponse &$response, $entity, $stock) {

}

/**
 * @} End of "addtogroup hooks".
 */
