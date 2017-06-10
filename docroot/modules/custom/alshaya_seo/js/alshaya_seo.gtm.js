/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  Drupal.behaviors.seoGoogleTagManager = {
    attach: function (context, settings) {

      // Global variables & selectors.
      var impressions = [];
      var body = $('body');
      var currencyCode = body.attr('gtm-currency');
      var gtmPageType = body.attr('gtm-container');
      var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]');
      var listName = body.attr('gtm-list-name');
      var removeCartSelector = $('a[gtm-type="gtm-remove-cart"]');
      var originalCartQty = 0;
      var updatedCartQty = 0;

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

      /** Impressions tracking on listing pages with Products. **/
      if ((gtmPageType === 'product detail page') || (gtmPageType === 'cart page')) {
        var count_pdp_items = 1;
        productLinkSelector.each(function() {
          // Fetch attributes for this product.
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          var pdpListName = '';
          var upSellCrossSellSelector = $(this).closest('.view-product-slider').parent('.views-element-container').parent();

          // Check whether the product is in US or CS region & update list accordingly.
          if (upSellCrossSellSelector.hasClass('horizontal-crossell')) {
            pdpListName = listName + '-CS';
          }
          else if (upSellCrossSellSelector.hasClass('horizontal-upell')) {
            pdpListName = listName + '-US';
          }

          impression.list = pdpListName;
          impression.position = count_pdp_items;
          impressions.push(impression);
          count_pdp_items++;
        });

        var data_pdp = {
          'event': 'productImpression',
          'ecommerce': {
            'currencyCode': currencyCode,
            'impressions': impressions
          }
        };

        dataLayer.push(data_pdp);
      }
      else if ($.inArray(gtmPageType, impressionPages)) {
        var count = 1;
        productLinkSelector.each(function() {
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          impression.list = listName;
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
            var addedProductSelector = '';
            var quantity = 1;
            var size = '';

            // If the add-to-cart button was triggered from modal, the target element will be modal.
            if ($(targetEl).hasClass('ui-dialog')) {
              addedProductSelector = $(targetEl).find('article[gtm-type="gtm-product-link"]');
              quantity = $(targetEl).find('form-item-quantity select').val();
            }
            else {
              addedProductSelector = $(targetEl).closest('article[gtm-type="gtm-product-link"]');
              quantity = $(targetEl).closest('.sku-base-form').find('.form-item-quantity select').val();
              size = $(targetEl).closest('.sku-base-form').find('.form-item-configurables-size select').val();
            }

            if (addedProductSelector) {
              var product = Drupal.alshaya_seo_gtm_get_product_values(addedProductSelector);
              // Remove product position: Not needed while adding to cart.
              delete product.position;

              // Set product quantity to selected quatity.
              product.quantity = quantity;

              // Set product size to selected size.
              product.dimension1 = size;

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

      /** Quantity update in cart. **/
      // Trigger removeFromCart & addToCart events based on the quantity update on cart page.
      $('select[gtm-type="gtm-quantity"]').focus(function() {
        originalCartQty = $(this).val();
      }).once('js-event').bind('change', function() {
        if (originalCartQty !== 0) {
          updatedCartQty = $(this).val();
          var diffQty = updatedCartQty - originalCartQty;
          var cartItem = $(this).closest('td.quantity').siblings('td.name').find('[gtm-type="gtm-remove-cart-wrapper"]');
          var product = Drupal.alshaya_seo_gtm_get_product_values(cartItem);
          var event = '';

          // Set updated product quantity.
          product.quantity = Math.abs(diffQty);

          //Set item's size as dimension1.
          product.dimension1 = cartItem.attr('gtm-size');

          // Remove product position: Not needed while updating item in cart.
          delete product.position;

          product.metric1 = product.quantity * product.price;

          if (diffQty < 0) {
            event = 'removeFromCart';
            product.metric1 = -1 * product.metric1;
          }
          else {
            event = 'addToCart';
          }

          var data = {
            'event': event,
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
      });

      /** Remove Product from cart **/
      // Add click handler to fire 'removeFromCart' event to GTM.
      removeCartSelector.each(function() {
        $(this).bind('click', function (e) {
          // Get selector holding details around the product.
          var removeItem = $(this).closest('td.quantity').siblings('td.name').find('[gtm-type="gtm-remove-cart-wrapper"]');
          var product = Drupal.alshaya_seo_gtm_get_product_values(removeItem);

          // Set product quantity to the number of items selected for quantity.
          product.quantity = $(this).closest('td.quantity').find('select').val();

          // Set selected size as dimension1.
          product.dimension1 = removeItem.attr('gtm-size');

          // Remove product position: Not needed while removing item from cart.
          delete product.position;

          product.metric1 = -1 * product.quantity * product.price;

          var data = {
            'event': 'removeFromCart',
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
        });
      });

      /** Reduce item count in cart **/

      /** Product Click Handler **/
      // Add click link handler to fire 'productClick' event to GTM.
      productLinkSelector.each(function () {
        $(this).bind('click', function (e) {
          var that = $(this);
          // Check the link triggering click & append sub-section to the listName if current page is
          // eligible for a sub-section in the list name.
          if ($.inArray(listName, pageSubListNames)) {
            // @TODO: Append sub-tag e.g., US & CS to the list name.
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
      'dimension1': '',
      'dimension2': '',
      'dimension3': product.attr('gtm-dimension3'),
      'dimension4': product.attr('gtm-stock'),
      'dimension5': product.attr('gtm-sku-type'),
      'metric1': product.attr('gtm-cart-value')
    };

    return productData;
  };
})(jQuery, Drupal, dataLayer);

