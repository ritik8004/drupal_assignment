<?php

/**
 * @file
 * Hooks specific to the alshaya_mobile_app module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter light product data.
 *
 * @param \Drupal\acq_sku\Entity\SKU $sku
 *   SKU object.
 * @param array $data
 *   Light product data that needs to be altered.
 *
 * @see \Drupal\alshaya_mobile_app\Service\MobileAppUtility::getLightProduct()
 */
function hook_alshaya_mobile_app_light_product_data_alter(\Drupal\acq_sku\Entity\SKU $sku, array &$data) {
  $test_data = [];
  $data['test'] = $test_data;
}
