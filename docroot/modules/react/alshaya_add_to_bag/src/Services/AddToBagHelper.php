<?php

namespace Drupal\alshaya_add_to_bag\Services;

use Drupal\acq_sku\CartFormHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * General Helper service for the Add To Bag feature.
 */
class AddToBagHelper {

  /**
   * Config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Cart form helper.
   *
   * @var \Drupal\acq_sku\CartFormHelper
   */
  protected $cartFormHelper;

  /**
   * Constructor for the AddToBagHelper service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\acq_sku\CartFormHelper $cart_form_helper
   *   Cart form helper.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    CartFormHelper $cart_form_helper
  ) {
    $this->configFactory = $config_factory;
    $this->cartFormHelper = $cart_form_helper;
  }

  /**
   * Detects if the Add To Bag feature is enabled or not.
   */
  public function isAddToBagFeatureEnabled() {
    return $this->configFactory->get('alshaya_add_to_bag.settings')->get('display_addtobag');
  }

  /**
   * Get product's info local storage expiration time.
   */
  public function getProductInfoLocalStorageExpiration() {
    return $this->configFactory->get('alshaya_add_to_bag.settings')->get('productinfo_local_storage_expiration');
  }

  /**
   * Sets common add to bag settings to build.
   *
   * @param array $build
   *   Page build array.
   */
  public function setAddToBagCommonSettingsToBuild(array &$build): void {
    if (_alshaya_seo_process_gtm()) {
      $build['#attached']['drupalSettings']['add_to_bag']['gtm_product_push'] = TRUE;
      $build['#attached']['library'][] = 'alshaya_seo_transac/gtm';
    }

    // Get the product's info expiration time in local storage.
    // Default to zero i.e. disable the local storage.
    $productinfo_local_storage_expiration = $this->getProductInfoLocalStorageExpiration();
    $build['#attached']['drupalSettings']['add_to_bag']['productinfo_local_storage_expiration'] = $productinfo_local_storage_expiration ?: 0;

    // Attach the theming library.
    $build['#attached']['library'][] = 'alshaya_white_label/plp-add-to-cart';

    // Attach notification library if feature is enabled.
    $build['#attached']['library'][] = 'alshaya_acm_cart_notification/cart_notification_js';

    // Attach cart utilities library if feature is enabled.
    $build['#attached']['library'][] = 'alshaya_spc/cart_utilities';

    // Check if the acm cart notification settings exist and assign notification
    // time in drupal settings.
    $acm_cart_notification_settings = $this->configFactory->get('alshaya_acm_cart_notification.settings');
    if ($acm_cart_notification_settings) {
      $build['#attached']['drupalSettings']['addToCartNotificationTime'] = $acm_cart_notification_settings->get('notification_time');
    }

    // Send the cart quantity options to be used while displaying quantity
    // dropdown. Adding the setting to global namespace.
    $build['#attached']['drupalSettings']['add_to_bag']['cart_quantity_options'] = _alshaya_acm_get_cart_quantity_options();
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'] ?? [], ['config:alshaya_acm.cart_config']);

    // Get the screen width from where we want to show configurable boxes.
    // Adding the setting to the global namespace.
    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    $build['#attached']['drupalSettings']['show_configurable_boxes_after'] = (int) $display_settings->get('show_configurable_boxes_after');
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'] ?? [], $display_settings->getCacheTags());

    // Add vat text.
    $product_settings = $this->configFactory->get('alshaya_acm_product.settings');
    $build['#attached']['drupalSettings']['vat_text'] = $product_settings->get('vat_text');
    $build['#attached']['drupalSettings']['is_all_products_buyable'] = (bool) $product_settings->get('all_products_buyable');
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'] ?? [], $product_settings->getCacheTags());

    // Pass max sale quantity status and whether message show be shown.
    $alshaya_acm_settings = $this->configFactory->get('alshaya_acm.settings');
    $build['#attached']['drupalSettings']['add_to_bag']['max_sale_quantity_enabled'] = $alshaya_acm_settings->get('quantity_limit_enabled');
    $build['#attached']['drupalSettings']['add_to_bag']['max_sale_hide_message'] = $alshaya_acm_settings->get('hide_max_qty_limit_message');
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'] ?? [], $alshaya_acm_settings->getCacheTags());

    // Pass config for showing/hiding quantity selector in configurable product
    // form.
    $build['#attached']['drupalSettings']['add_to_bag']['show_quantity'] = $this->cartFormHelper->showQuantity();
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'] ?? [], ['config:acq_sku.configurable_form_settings']);
  }

}
