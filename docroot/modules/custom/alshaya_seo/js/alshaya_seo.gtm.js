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
      var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
      var listName = body.attr('gtm-list-name');
      var removeCartSelector = $('a[gtm-type="gtm-remove-cart"]', context);
      var cartCheckoutLoginSelector = $('body[gtm-container="cart-checkout-login"]');
      var cartCheckoutDeliverySelector = $('body[gtm-container="cart-checkout-delivery"]');
      var cartCheckoutPaymentSelector = $('body[gtm-container="cart-checkout-payment"]');
      var topNavLevelOneSelector = $('li.menu--one__list-item', context);
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
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';

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

        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, impressions);
      }
      else if ($.inArray(gtmPageType, impressionPages) !== -1) {
        var count = 1;
        productLinkSelector.each(function() {
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          impression.list = listName;
          impression.position = count;
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          impressions.push(impression);
          count++;
        });

        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, impressions);
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
              quantity = parseInt($(targetEl).find('.form-item-quantity select').val());
              size = $(targetEl).find('.form-item-configurables-size select option:selected').text();
            }
            else {
              addedProductSelector = $(targetEl).closest('article[gtm-type="gtm-product-link"]');
              quantity = parseInt($(targetEl).closest('.sku-base-form').find('.form-item-quantity select').val());
              size = $(targetEl).closest('.sku-base-form').find('.form-item-configurables-size select option:selected').text();
            }

            if (addedProductSelector) {
              var product = Drupal.alshaya_seo_gtm_get_product_values(addedProductSelector);
              // Remove product position: Not needed while adding to cart.
              delete product.position;

              // Set product quantity to selected quatity.
              product.quantity = quantity;

              // Set product size to selected size.
              if (product.dimension5 !== 'simple') {
                product.dimension1 = size;
              }

              // Set product variant to the selected variant.
              if (product.dimension5 !== 'simple') {
                product.variant = $('.selected-variant-sku-' + product.id.toLowerCase()).val();
              }
              else {
                product.variant = product.id;
              }

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
      }).once('js-event').on('change', function() {
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
      removeCartSelector.once('js-event').each(function() {
        $(this).on('click', function (e) {
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

      /** Tracking New customers **/
      cartCheckoutLoginSelector.find('a[gtm-type="checkout-as-guest"]').once('js-event').on('click', function() {
        Drupal.alshaya_seo_gtm_push_customer_type('New Customer');
      });

      /** Tracking Returning customers **/
      cartCheckoutLoginSelector.find('input[gtm-type="checkout-signin"]').once('js-event').on('click', function() {
        Drupal.alshaya_seo_gtm_push_customer_type('Returning Customers');
      });

      /** Tracking Home Delivery **/
      if (cartCheckoutDeliverySelector.length !== 0) {
        // Fire checkout option event if home delivery option is selected by default on delivery page.
        if (cartCheckoutDeliverySelector.find('div[gtm-type="checkout-home-delivery"]').once('js-event').hasClass('active--tab--head')) {
          Drupal.alshaya_seo_gtm_push_delivery_type('Home Delivery');
        }
        // Fire checkout option event when user switches delivery option.
        cartCheckoutDeliverySelector.find('[data-drupal-selector="edit-delivery-tabs"] .tab').once('js-event').each(function() {
          $(this).on('click', function() {
            var gtmType = $(this).attr('gtm-type');
            var deliveryType = '';
            if (gtmType !== undefined) {
              if (gtmType === 'checkout-home-delivery') {
                deliveryType = 'Home Delivery';
              }
              else if (gtmType === 'checkout-click-collect') {
                deliveryType = 'Click & Collect';
              }

              Drupal.alshaya_seo_gtm_push_delivery_type(deliveryType);
            }
          });
        });
      }

      /** Tracking selected payment option **/
      if (cartCheckoutPaymentSelector.length !== 0) {
        var preselectedMethod = $('[gtm-type="cart-checkout-payment"] input:checked');
        if (preselectedMethod.length === 1) {
          var preselectedMethodLabel = preselectedMethod.siblings('label').text();
          Drupal.alshaya_seo_gtm_push_selected_payment(preselectedMethodLabel);
        }

        $('[gtm-type="cart-checkout-payment"] input', context).once('js-event').change(function() {
          var selectedMethod = $('[gtm-type="cart-checkout-payment"] input:checked');
          if (selectedMethod === 1) {
            var selectedMethodLabel = selectedMethod.siblings('label').text();
            Drupal.alshaya_seo_gtm_push_selected_payment(selectedMethodLabel);
          }
        });
      }

      /** Product Click Handler **/
      // Add click link handler to fire 'productClick' event to GTM.
      productLinkSelector.each(function () {
        $(this).on('click', function (e) {
          var that = $(this);
          var product = Drupal.alshaya_seo_gtm_get_product_values(that);
          product.variant = '';
          var data = {
            'event': 'productClick',
            'ecommerce': {
              'currencyCode': currencyCode,
              'click': {
                'actionField': {'list': listName},
                'products': [product]
              }
            }
          };

          dataLayer.push(data);
        });
      });

      /** Tracking internal promotion impressions. **/
      topNavLevelOneSelector.once('js-event').on('mouseenter', function() {
        if ($(this).hasClass('has-child')) {
          var topNavLevelTwo = $(this).children('ul.menu--two__list');
          var topNavLevelThree = topNavLevelTwo.children('li.has-child').children('ul.menu--three__list');
          var highlights = [];

          if ((topNavLevelThree.length > 0) && (topNavLevelThree.children('.highlights'))) {
            highlights = topNavLevelThree.children('.highlights').find('[gtm-type="gtm-highlights"]');
          }
          if (highlights.length > 0) {
            Drupal.alshaya_seo_gtm_push_promotion_impressions(highlights, gtmPageType);
          }
        }

      });

      $('[gtm-type="gtm-highlights"]').once('js-event').on('click', function() {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), gtmPageType, 'promotionClick');
      });

      /** Tracking clicks on fitler & sort options. **/
      if (listName === "PLP" || listName === "Search Results Page") {
        var section = listName;
        if (listName === 'PLP') {
          section = $('h1.c-page-title').text().toLowerCase();
        }

        // Track facet filters.
        $('li.facet-item', context).once('js-event').on('click', function() {
          var selectedVal = $(this).find('label>span.facet-item__value').text();
          var facetTitle = $(this).parent('ul').siblings('h3.c-facet__title').text();
          var filterValue = facetTitle + ':' + selectedVal;

          var data = {
            'event' : 'filter',
            'section' : section,
            'filterValue': filterValue
          };

          dataLayer.push(data);
        });

        // Track sorts.
        $('select[name="sort_bef_combine"]').on('change', function() {
          var sortValue = $(this).find('option:selected').text();
          var data = {
            'event' : 'sort',
            'section' : section,
            'sortValue': sortValue
          };

          dataLayer.push(data);
        });
      }
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
  
  Drupal.alshaya_seo_gtm_push_customer_type = function (customerType) {
    var data = {
      'event': 'checkoutOption',
      'ecommerce': {
        'checkout_option': {
          'actionField': {
            'step': 1,
            'option': customerType
          }
        }
      },
      'eventCallback': function() {
        document.location = 'cart/checkout/delivery';
      }
    };
    
    dataLayer.push(data);
  };

  Drupal.alshaya_seo_gtm_push_selected_payment = function(paymentMethod) {
    var data = {
      'event': 'checkoutOption',
      'ecommerce': {
        'checkout_option': {
          'actionField': {
            'step': 4,
            'option': paymentMethod
          }
        }
      },
      'eventCallback': function () {
        document.location = 'cart/checkout/confirmation';
      }
    };
    
    dataLayer.push(data);
  };

  Drupal.alshaya_seo_gtm_push_delivery_type = function(deliveryType) {
    var data = {
      'event': 'checkoutOption',
      'ecommerce': {
        'checkout_option': {
          'actionField': {
            'step': 2,
            'option': deliveryType
          }
        }
      },
      'eventCallback': function() {
        document.localtion = 'cart/checkout/payment';
      }
    };

    dataLayer.push(data);
  };

  Drupal.alshaya_seo_gtm_push_impressions = function(currencyCode, impressions) {
    if (impressions.length > 0) {
      var data = {
        'event': 'productImpression',
        'ecommerce': {
          'currencyCode': currencyCode,
          'impressions': impressions
        }
      };

      dataLayer.push(data);
    }
  };

  Drupal.alshaya_seo_gtm_push_promotion_impressions = function(highlights, gtmPageType, event) {
    var promotions = [];

    highlights.each(function(key, highlight) {
      var promotion = {
        'id': '',
        'name': gtmPageType,
        'creative': '',
        'position': 'slot' + key+1
      };
      promotions.push(promotion);
    });

    var data = {
      'ecommerce': {
        'promoView': {
          'promotions': promotions
        }
      }
    };

    if (event === 'promotionClick') {
      var location = '';

      if (highlights.find('a').length !== 0) {
        location = highlights.find('a').attr('href');
      }
      else {
        location = window.location.pathname;
      }

      data = {
        'event': event,
        'ecommerce': {
          'promoClick': {
            'promotions': promotions
          }
        },
        'eventCallback': function() {
          document.location = location;
        }
      };

    }

    dataLayer.push(data);
  };

})(jQuery, Drupal, dataLayer);
