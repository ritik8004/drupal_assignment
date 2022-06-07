<?php

/**
 * @file
 * Hooks specific to the alshaya_acm module.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_cart\CartInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow other modules to modify remove from Basket link for a cart item.
 *
 * @param \Drupal\Core\Url|null $remove_url
 *   URL object.
 * @param \Drupal\acq_cart\CartInterface $cart
 *   Cart object.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU for cart item.
 */
function hook_alshaya_acm_get_remove_from_basket_link_alter(&$remove_url, CartInterface $cart, SKUInterface $sku) {

}

/**
 * Allow other modules to specify which configs should be reset.
 *
 * @param array $reset
 *   Configs to reset from Settings.
 */
function hook_alshaya_reset_config_configs_to_reset_alter(array &$reset) {
  $reset[] = 'alshaya_media_assets.settings';
}

/**
 * Add configs to be reset when switching to different Magento instance.
 *
 * @param array $configs
 *   Configs to be reset.
 */
function hook_alshaya_acm_switch_magento_configs_alter(array &$configs) {
  // We need pims_base_url to be set per Magento instance.
  // We add below for configs to be switched so it is set for Magento instance.
  $configs['alshaya_media_assets.settings'] = 'pims_base_url';
}

/**
 * @} End of "addtogroup hooks".
 */
