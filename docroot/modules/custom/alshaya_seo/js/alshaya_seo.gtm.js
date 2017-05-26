/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManager = {
    attach: function (context, settings) {
      $('[gtm-type="gtm-product-link"]').once('gtm-jsevent').each(function () {
        $(this).bind('click', function (e) {
          e.preventDefault();

          var that = $(this);
          try {
            var data = {
              'event': 'productClick',
              'ecommerce': {
                'currencyCode': $('body').attr('gtm-currency'),
                'click': {
                  'actionField': {'list': that.parents('.gtm-container:first').attr('gtm-container-id')},
                  'products': [Drupal.alshaya_seo_gtm_get_product_values(that)]
                }
              },
              'eventCallback': function () {
                document.location = that.attr('href');
              }
            };

            dataLayer.push(data);
          }
          catch (e) {
            // @TODO: Remove this once we finish the implementation.
            console.log(e);
          }
        });
      });
    }
  };

  /**
   * Function to provide product data object.
   *
   * @param product
   *   jQuery object which contains all gtm attributes.
   */
  Drupal.alshaya_seo_gtm_get_product_values = function (product) {
    var productData = {
      'name': product.attr('gtm-name'),
      'id': product.attr('gtm-main-sku'),
      'price': parseFloat(product.attr('gtm-price')),
      'brand': product.attr('gtm-brand'),
      'category': product.attr('gtm-category'),
      'variant': product.attr('gtm-product-sku'),
      'position': 1,
      'dimension1': product.attr('gtm-dimension1'),
      'dimension2': product.attr('gtm-dimension2'),
      'dimension3': product.attr('gtm-dimension3'),
      'dimension4': product.attr('gtm-stock'),
      'dimension5': product.attr('gtm-sku-type'),
      'metric1': product.attr('gtm-cart-value')
    };

    return productData;
  }
})(jQuery);
