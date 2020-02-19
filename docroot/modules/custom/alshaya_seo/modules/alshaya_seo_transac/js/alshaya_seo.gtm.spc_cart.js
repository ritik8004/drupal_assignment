/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.behaviors.spcCartGtm = {
    attach: function (context, settings) {
      Drupal.alshaya_spc_cart_gtm();
      document.addEventListener('refreshCart', function (e) {
        Drupal.alshaya_spc_cart_gtm();
      });

      document.addEventListener('updateCart', function (e) {
        var gtmEvent = '';
        var item = e.detail.data.item;
        var qty = e.detail.data.qty;
        if (item.qty > qty) {
          item.qty = item.qty - qty;
          gtmEvent = 'removeFromCart';
          Drupal.alshaya_spc_cart_gtm_update_cart(item, gtmEvent);
        }
        else if (item.qty < qty) {
          item.qty = qty - item.qty;
          gtmEvent = 'addToCart';
          Drupal.alshaya_spc_cart_gtm_update_cart(item, gtmEvent);
        }

      });
    }
  };

  Drupal.alshaya_spc_cart_gtm = function() {
    // GTM data for SPC cart.
    if (localStorage.hasOwnProperty('cart_data')) {
      var cart_data = JSON.parse(localStorage.getItem('cart_data'));
      dataLayer[0].productSKU = [];
      dataLayer[0].productStyleCode = [];
      dataLayer[0].cartTotalValue = cart_data.cart.cart_total;
      dataLayer[0].cartItemsCount = cart_data.cart.items_qty;
      var items = cart_data.cart.items;
      dataLayer[0].ecommerce.checkout.products = [];
      dataLayer[0].cartItemsFlocktory = [];
      Object.entries(items).forEach(([key, product]) => {
        dataLayer[0].productStyleCode.push(product.parent_sku);
        dataLayer[0].productSKU.push(key);
        var productData = Drupal.alshaya_spc_gtm_product(product);
        dataLayer[0].ecommerce.checkout.products.push(productData);
        var flocktory = {
          id: product.parent_sku,
          price: product.final_price,
          count: product.qty,
          title: product.gtm_attributes['gtm-name'],
          image: product.extra_data.cart_image,
        };
        dataLayer[0].cartItemsFlocktory.push(flocktory);
      });
    }
  };

  Drupal.alshaya_spc_cart_gtm_update_cart = function (product, gtmEvent) {
    var productData = {
      event: gtmEvent,
      ecommerce: {
        currencyCode: drupalSettings.alshaya_spc.currency_config.currency_code,
        add: {
          product: []
        }
      }
    };
    var productDetails = Drupal.alshaya_spc_gtm_product(product);
    productDetails.metric2 = product.final_price;
    productData.ecommerce.add.product.push(productDetails);
    dataLayer.push(productData);
  };

  Drupal.alshaya_spc_gtm_product = function (product) {
    var attributes = product.attributes;
    var i = 0;
    for (i = 0; i < attributes.length; i++) {
      if (attributes[i]['key'] === 'product_brand') {
        var productBrand = attributes[i]['value'];
      }
    }
    var productDetails = {
      name: product.gtm_attributes['gtm-name'],
      id: product.parent_sku,
      price: product.final_price,
      brand: productBrand,
      category: product.gtm_attributes['gtm-category'],
      variant: product.sku,
      quantity: product.qty,
    };
    productDetails.dimension1 = product.gtm_attributes.dimension1;
    if (product.hasOwnProperty('configurable_values')) {
      productDetails.dimension2 = 'configurable';
    }
    if (product.final_price != product.original_price) {
      productDetails.dimension3 = 'discounted';
    }
    if (product.gtm_attributes.hasOwnProperty('dimension4')) {
      productDetails.dimension4 = product.gtm_attributes.dimension4;
    }
    if (product.gtm_attributes.hasOwnProperty('dimension5')) {
      productDetails.dimension5 = product.gtm_attributes.dimension5;
    }
    if (product.gtm_attributes.hasOwnProperty('dimension6')) {
      productDetails.dimension6 = product.gtm_attributes.dimension6;
    }
    return productDetails;
  };

})(jQuery, Drupal, dataLayer);
