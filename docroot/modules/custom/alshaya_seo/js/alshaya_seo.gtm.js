/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManager = {
    attach: function (context, settings) {

      var impressions = [];
      var body = $('body');
      var currencyCode = body.attr('gtm-currency');
      var gtmPageType = body.attr('gtm-container');
      var productLinkSelector = $('[gtm-type="gtm-product-link"]');

      // List of Pages where we need to push out list of product being rendered to GTM.
      var impressionPages = [
        'home page',
        'search result page',
        'product listing page',
        'product detail page',
        'department page',
      ];

      if ($.inArray(gtmPageType, impressionPages)) {
        var count = 1;
        productLinkSelector.once('gtm-js-event').each(function() {
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          impression.list = gtmPageType;
          impression.position = count;
          impressions.push(impression);
          count++;
        });

        var data = {
          'event': 'productImpression',
          'ecommerce': {
            'currencyCode': currencyCode,
            'impressions': impressions
          }
        };

        dataLayer.push(data);
      }

      productLinkSelector.once('gtm-jsevent').each(function () {
        $(this).bind('click', function (e) {
          var that = $(this);
          try {
            var data = {
              'event': 'productClick',
              'ecommerce': {
                'currencyCode': currencyCode,
                'click': {
                  'actionField': {'list': that.attr('gtm-container')},
                  'products': [Drupal.alshaya_seo_gtm_get_product_values(that)]
                }
              },
              'eventCallback': function () {
                document.location = that.attr('about');
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
