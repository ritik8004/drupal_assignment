<?php

/**
 * @file
 * Includes all utility functions are GTM.
 */

use Drupal\acq_sku\Entity\SKU;
use Drupal\node\Entity\Node;

/**
 * Helper function to prepare attributes for a product.
 *
 * @param \Drupal\node\Entity\Node $product
 *   Node object for which we want to get the attributes prepared.
 *
 * @return array
 *   Attributes array.
 */
function alshaya_seo_gtm_prepare_product_attributes(Node $product) {
  $sku = SKU::loadFromSku($product->get('field_skus')->first()->getString());

  $attributes = [];

  $attributes['gtm-type'] = 'gtm-product-link';
  $attributes['gtm-name'] = $sku->label();
  $attributes['gtm-main-sku'] = $sku->getSku();

  $price = $sku->get('price')->getString() ? $sku->get('price')->getString() : $sku->get('final_price')->getString();
  $attributes['gtm-price'] = number_format($price, 3);

  // @TODO: Is this site name?
  $attributes['gtm-brand'] = '';

  $attributes['gtm-category'] = '';

  // @TODO: We should find a way to get this function work for other places.
  $attributes['gtm-product-sku'] = '';

  // @TODO: This is getting static, need to find a way or discuss.
  $attributes['gtm-dimension1'] = $sku->get('attr_size')->getString();
  $attributes['gtm-dimension2'] = '';
  $attributes['gtm-dimension3'] = 'Baby Clothing';
  $attributes['gtm-stock'] = alshaya_acm_is_product_in_stock($sku->getSku()) ? 'in stock' : 'out of stock';
  $attributes['gtm-sku-type'] = $sku->bundle();

  // @TODO: We should find a way to get this function work for other places.
  $attributes['gtm-cart-value'] = '';

  return $attributes;
}

/**
 * Helper function to convert attributes array to string.
 *
 * @param array $attributes
 *   Attributes array.
 *
 * @return string
 *   Attributes string to be displayed directly in twig.
 */
function alshaya_seo_gtm_prepare_product_attributes_prepare_string(array $attributes) {
  $attributes_string = ' ';

  foreach ($attributes as $key => $value) {
    $attributes_string .= $key;
    $attributes_string .= '=';
    $attributes_string .= '"' . $value . '"';
    $attributes_string .= ' ';
  }

  return $attributes_string;
}
