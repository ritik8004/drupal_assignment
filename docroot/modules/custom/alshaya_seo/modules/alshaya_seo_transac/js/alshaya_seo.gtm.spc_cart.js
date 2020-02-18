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
        var attributes = product.attributes;
        var i = 0;
        for (i = 0; i < attributes.length; i++) {
          if (attributes[i]['key'] === 'product_brand') {
            var productBrand = attributes[i]['value'];
          }
        }

        dataLayer[0].productStyleCode.push(product.parent_sku);
        dataLayer[0].productSKU.push(key);

        var productData = {
          name: product.gtm_attributes['gtm-name'],
          id: product.id,
          price: product.final_price,
          brand: productBrand,
          category: product.gtm_attributes['gtm-category'],
          variant: key,
          quantity: product.qty,
        };

        productData.dimension1 = product.gtm_attributes.dimension1;

        if (product.hasOwnProperty('configurable_values')) {
          productData.dimension2 = 'configurable';
        }

        if (product.final_price != product.original_price) {
          productData.dimension3 = 'discounted';
        }

        if (product.gtm_attributes.hasOwnProperty('dimension4')) {
          productData.dimension4 = product.gtm_attributes.dimension4;
        }

        if (product.gtm_attributes.hasOwnProperty('dimension5')) {
          productData.dimension5 = product.gtm_attributes.dimension5;
        }

        if (product.gtm_attributes.hasOwnProperty('dimension6')) {
          productData.dimension6 = product.gtm_attributes.dimension6;
        }

        dataLayer[0].ecommerce.checkout.products.push(productData);

        var flocktory = {
          id: product.id,
          price: product.final_price,
          count: product.qty,
          title: product.gtm_attributes['gtm-name'],
          image: product.extra_data.cart_image,
        };
        dataLayer[0].cartItemsFlocktory.push(flocktory);

      });
    }
  };

})(jQuery, Drupal, dataLayer);
