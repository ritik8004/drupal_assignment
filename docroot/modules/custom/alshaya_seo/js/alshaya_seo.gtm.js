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
        // @TODO: Calculate impressions separately for PDP since they reside under US & CS regions.
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

      /** Add to cart GTM **/
      // Trigger GTM push event on AJAX completion of add to cart button.
      $(document).ajaxComplete(function(event, xhr, settings) {
        if ((settings.hasOwnProperty('extraData')) && (settings.extraData._triggering_element_value === "Add to cart")) {
          var responseJSON = xhr.responseJSON;
          var responseMessage = '';
          $.each(responseJSON, function(key, obj) {
            if (obj.method === 'stopSpinner') {
              responseMessage = obj.args[0].message;
            }
          });

          // Only trigger gtm push event for cart if product added to cart successfully.
          if (responseMessage === 'success') {
            var targetEl = event.target.activeElement;
            var addedProductSelector = $(targetEl).closest('article[gtm-type="gtm-product-link"]');
            if (addedProductSelector) {
              var product = Drupal.alshaya_seo_gtm_get_product_values(addedProductSelector);
              // Remove product position: Not needed while adding to cart.
              delete product.position;

              // Set product quantity to 1 since we are adding item to cart here.
              product.quantity = 1;

              // Calculate metric 1 value.
              product.metric1 = product.price * product.quantity;

              var data = {
                'event': 'addToCart',
                'ecommerce': {
                  'currencyCode': currencyCode,
                  'add': {
                    'products': [
                      product
                    ]
                  }
                }
              };

              dataLayer.push(data);
            }
          }
        }
      });

      productLinkSelector.each(function () {
        $(this).bind('click', function (e) {
          var that = $(this);
          // Check the link triggering click & append sub-section to the listName if current page is
          // eligible for a sub-section in the list name.
          if ($.inArray(listName, pageSubListNames)) {

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

