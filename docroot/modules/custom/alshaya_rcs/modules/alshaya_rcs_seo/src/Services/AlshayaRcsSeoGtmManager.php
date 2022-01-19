<?php

namespace Drupal\alshaya_rcs_seo\Services;

use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_seo_transac\AlshayaGtmManager;

/**
 * Class Alshaya RCS Seo Gtm Manager.
 *
 * The class extends \Drupal\alshaya_seo_transac\AlshayaGtmManager to override
 * functions to make them compatible to V3.
 */
class AlshayaRcsSeoGtmManager extends AlshayaGtmManager {

  /**
   * {@inheritDoc}
   */
  public function fetchSkuAtttributes($skuId, SKUInterface $child = NULL, $parentSku = NULL) {
    $gtm_disabled_vars = $this->configFactory->get('alshaya_seo.disabled_gtm_vars')->get('disabled_vars');

    $attributes['gtm-name'] = '#rcs.product.gtmAttributes.name#';
    $attributes['gtm-product-sku'] = '#rcs.product.sku#';
    $attributes['gtm-product-sku-class-identifier'] = '#rcs.product._self|sku-clean#';
    $attributes['gtm-sku-type'] = '#rcs.product.skuType#';

    // Dimension1 & 2 correspond to size & color.
    // Should stay blank unless added to cart.
    if (!in_array('dimension1', $gtm_disabled_vars)) {
      $attributes['gtm-dimension1'] = '#rcs.product.gtmAttributes.dimension1#';
    }
    if (!in_array('dimension5', $gtm_disabled_vars)) {
      $attributes['gtm-dimension5'] = '#rcs.product.gtmAttributes.dimension5#';
    }
    if (!in_array('dimension6', $gtm_disabled_vars)) {
      $attributes['gtm-dimension6'] = '#rcs.product.gtmAttributes.dimension6#';
    }
    $attributes['gtm-dimension4'] = '#rcs.product.image#';
    $attributes['gtm-dimension3'] = '#rcs.product.gtmAttributes.dimension3#';

    $attributes['gtm-price'] = '#rcs.product.gtmAttributes.price#';
    if (!in_array('brand', $gtm_disabled_vars)) {
      $attributes['gtm-brand'] = '#rcs.product.gtmAttributes.brand#';
    }
    // @todo This is supposed to stay blank here?
    $attributes['gtm-stock'] = '';
    $attributes['gtm-category'] = '#rcs.product.gtmAttributes.category#';
    $attributes['gtm-main-sku'] = $parentSku ? $parentSku : $skuId;
    // Add these temporary values so these can be used in front end to make
    // API calls.
    $attributes['gtm-temp-sku'] = $skuId;
    $attributes['gtm-temp-parentSku'] = $parentSku;

    return $attributes;
  }

  /**
   * {@inheritDoc}
   */
  public function convertHtmlAttributesToDatalayer($attributes) {
    $product_datalayer_attributes = parent::convertHtmlAttributesToDatalayer($attributes);
    $product_datalayer_attributes['gtm-temp-sku'] = $attributes['gtm-temp-sku'];
    $product_datalayer_attributes['gtm-temp-parentSku'] = $attributes['gtm-temp-parentSku'];

    return $product_datalayer_attributes;
  }

}
