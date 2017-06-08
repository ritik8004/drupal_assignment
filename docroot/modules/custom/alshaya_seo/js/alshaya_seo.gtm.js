/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManager = {
    attach: function (context, settings) {

      var impressions = [];
      var body = $('body');
      var currencyCode = body.attr('gtm-currency');
      var gtmPageType = body.attr('gtm-container');
      var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"]');
      var cartLinkSelector = $('article [gtm-type="add-cart-link"]');
      var listName = body.attr('gtm-list-name');

      // List of Pages where we need to push out list of product being rendered to GTM.
      var impressionPages = [
        'home page',
        'search result page',
        'product listing page',
        'product detail page',
        'department page'
      ];

      // Pages for which there are sections triggering click. Cross-sell/Up-sell section on Product detail pages.
      var pageSubListNames = [
        'PDP'
      ];

      // If we receive an empty page type, set page type as not defined.
      if (gtmPageType === undefined) {
        gtmPageType = 'not defined';
      }

      if (gtmPageType === 'product detail page') {
        cartLinkSelector.each(function() {
          $(this).bind('click', function(e) {
            dataLayer.push({'hello':'world'});
          });
        });
      }
      else if ($.inArray(gtmPageType, impressionPages)) {
        var count = 1;
        productLinkSelector.each(function() {
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

      productLinkSelector.each(function () {
        $(this).bind('click', function (e) {
          console.log($(this));
          alert('hello');
          var that = $(this);
          // Check the link triggering click & append sub-section to the listName if current page is
          // eligible for a sub-section in the list name.
          if ($.inArray(listName, pageSubListNames)) {
            console.log(that.closest('.views-element-container'));
          }

          try {
            var data = {
              'event': 'productClick',
              'ecommerce': {
                'currencyCode': currencyCode,
                'click': {
                  'actionField': {'list': listName},
                  'products': [Drupal.alshaya_seo_gtm_get_product_values(that)]
                }
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
  };
})(jQuery, Drupal, dataLayer);

