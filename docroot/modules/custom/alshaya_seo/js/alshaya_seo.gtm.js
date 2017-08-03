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
      var cartCheckoutLoginSelector = $('body[gtm-container="summary page"]');
      var cartCheckoutDeliverySelector = $('body[gtm-container="delivery page"]');
      var cartCheckoutPaymentSelector = $('body[gtm-container="payment page"]');
      var subDeliveryOptionSelector = $('#shipping_methods_wrapper .shipping-methods-container .js-webform-radios', context);
      var topNavLevelOneSelector = $('li.menu--one__list-item', context);
      var isCCPage = false;
      var isPaymentPage = false;
      var isRegistrationPage = false;
      var isRegistrationSuccessPage = false;
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

      // If we are on checkout page -- Click & collect method.
      if (document.location.search === "?method=cc") {
        isCCPage = true;
      }

      // If we are on payment page.
      if (document.location.pathname === Drupal.url('cart/checkout/payment')) {
        isPaymentPage = true;
      }

      // If we are on registration page.
      if (document.location.pathname === Drupal.url('user/register')) {
        isRegistrationPage = true;
      }

      if (document.location.pathname === Drupal.url('user/register/complete')) {
        isRegistrationSuccessPage = true;
      }

      if (isRegistrationSuccessPage) {
        Drupal.alshaya_seo_gtm_push_signin_type('registration success');
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
      $(document).once('js-event').ajaxComplete(function(event, xhr, settings) {
        if ((settings.hasOwnProperty('extraData')) && (settings.extraData.hasOwnProperty('_triggering_element_value')) &&  (settings.extraData._triggering_element_value.toLowerCase() === Drupal.t('sign up').toLowerCase())) {
          var responseJSON = xhr.responseJSON;
          var responseMessage = '';
          $.each(responseJSON, function(key, obj) {
            if (obj.method === 'stopNewsletterSpinner') {
              responseMessage = obj.args[0].message;
              return false;
            }
          });

          if (responseMessage === "success") {
            Drupal.alshaya_seo_gtm_push_lead_type('footer');
          }
        }
      });

      /** Newsletter subscription tracking on Registration page. **/
      if (isRegistrationPage) {
        $('input[name="field_subscribe_newsletter[value]"]').change(function() {
          if ($(this).is(':checked')) {
            Drupal.alshaya_seo_gtm_push_lead_type('registration');
          }
        });
      }

      /** Sub-delivery option virtual page tracking. **/
      if (subDeliveryOptionSelector.text() !== '') {
        Drupal.alshaya_seo_gtm_push_virtual_checkout_option();
        var checkout_subdl = '';
        for( var i=0; i<dataLayer.length; i++) {
          if (dataLayer[i].event === 'checkout') {
            checkout_subdl = dataLayer[i];
            break;
          }
        }
        checkout_subdl.ecommerce.checkout.actionField.step = 3;
        dataLayer.push(checkout_subdl);
      }

      subDeliveryOptionSelector.find('.form-type-radio').once('js-event').each(function() {
        // Push default selected sub-delivery option to GTM.
        if ($(this).find('input[checked="checked"]').length > 0) {
          var selectedMethodLabel = $(this).find('.shipping-method-title').text();
          Drupal.alshaya_seo_gtm_push_checkout_option(selectedMethodLabel, 3);
        }

        // Attach change event listener to shipping method radio buttons.
        $(this).change(function() {
          var selectedMethodLabel = $(this).find('.shipping-method-title').text();
          Drupal.alshaya_seo_gtm_push_checkout_option(selectedMethodLabel, 3);
        });
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
          Drupal.alshaya_seo_gtm_push_checkout_option('Home Delivery', 2);
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

              Drupal.alshaya_seo_gtm_push_checkout_option(deliveryType, 2);
            }
          });
        });
      }

      /** GTM virtual page tracking for click & collect journey. **/
      if (isCCPage) {
        if ($('#store-finder-wrapper', context).length > 0) {
          dataLayer.push({
            'event':'VirtualPageview',
            'virtualPageURL':'/virtualpv/click-and-collect/step1/click-and-collect-view',
            'virtualPageTitle' : 'C&C Step 1 – Click and Collect View'
          });

          Drupal.alshaya_seo_gtm_push_checkout_option('Click & Collect', 2);
        }

        $('.store-actions a.select-store', context).once('js-event').click(function() {
          dataLayer.push({
            'event':'VirtualPageview',
            'virtualPageURL':' /virtualpv/click-and-collect/step2/select-store',
            'virtualPageTitle' : 'C&C Step 2 – Select Store'
          });
        });
      }

      if (isPaymentPage) {
        // Check delivery type.
        var deliveryType = $('#block-checkoutsummaryblock .delivery-type')
          .clone()    //clone the element
          .children() //select all the children
          .remove()   //remove all the children
          .end()  //again go back to selected element
          .text()
          .trim()
          .toLowerCase();

        if (deliveryType === Drupal.t('Click & Collect').toLowerCase()) {
          dataLayer.push({
            'event':'VirtualPageview',
            'virtualPageURL':'/virtualpv/click-and-collect/step3/payment-page',
            'virtualPageTitle' : 'C&C Step 3 – Payment Page'
          });
        }
      }

      /** Tracking selected payment option **/
      // Fire this only if on checkout Payment option page & Ajax response brings in cart-checkout-payment div.
      if ((cartCheckoutPaymentSelector.length !== 0) && ($('fieldset[gtm-type="cart-checkout-payment"]', context).length > 0)) {
        var preselectedMethod = $('[gtm-type="cart-checkout-payment"] input:checked');
        if (preselectedMethod.length === 1) {
          var preselectedMethodLabel = preselectedMethod.siblings('label').find('.method-title').text();
          Drupal.alshaya_seo_gtm_push_checkout_option(preselectedMethodLabel, 4);
        }
      }

      /** Product Click Handler **/
      // Add click link handler to fire 'productClick' event to GTM.
      productLinkSelector.each(function () {
        $(this).once('js-event').on('click', function (e) {
          var that = $(this);
          Drupal.alshaya_seo_gtm_push_product_clicks(that, currencyCode, listName);
        });
      });

      /** Product click handler for Modals. **/
      // Add click link handler to fire 'productClick' event to GTM.
      $('a[href*="product-quick-view"]').each(function() {
        $(this).once('js-event').on('click', function (e) {
          var that = $(this).closest('article[data-vmode="teaser"]');
          Drupal.alshaya_seo_gtm_push_product_clicks(that, currencyCode, listName);
        });
      });

      /** Tracking internal promotion impressions. **/
      // Tracking menu level promotions
      topNavLevelOneSelector.once('js-event').on('mouseenter', function() {
        if ($(this).hasClass('has-child')) {
          var topNavLevelTwo = $(this).children('ul.menu--two__list');
          var topNavLevelThree = topNavLevelTwo.children('li.has-child').children('ul.menu--three__list');
          var highlights = [];

          if ((topNavLevelThree.length > 0) && (topNavLevelThree.children('.highlights'))) {
            highlights = topNavLevelThree.children('.highlights').find('[gtm-type="gtm-highlights"]');
          }
          if (highlights.length > 0) {
            Drupal.alshaya_seo_gtm_push_promotion_impressions(highlights, 'Top Navigation');
          }
        }

      });

      $('[gtm-type="gtm-highlights"]').once('js-event').on('click', function() {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), 'Top Navigation', 'promotionClick');
      });

      if ($('.paragraph--type--promo-block').length > 0) {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($('.paragraph--type--promo-block'), gtmPageType);
      }

      // Tracking view of promotions.
      $('.paragraph--type--promo-block').each(function() {
        $(this).once('js-event').on('click', function() {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), gtmPageType, 'promotionClick');
        });
      });
      /** Tracking clicks on fitler & sort options. **/
      if (listName === "PLP" || listName === "Search Results Page") {
        var section = listName;
        if (listName === 'PLP') {
          section = $('h1.c-page-title').text().toLowerCase();
        }

        // Track facet filters.
        $('li.facet-item').once('js-event').on('click', function() {
          if ($(this).find('input.facets-checkbox').attr('checked') === undefined) {
            var selectedVal = $(this).find('label>span.facet-item__value').text();
            var facetTitle = $(this).parent('ul').siblings('h3.c-facet__title').text();
            var filterValue = facetTitle + ':' + selectedVal;

            var data = {
              'event' : 'filter',
              'section' : section,
              'filterValue': filterValue
            };

            dataLayer.push(data);
          }
        });

        // Track sorts.
        $('select[name="sort_bef_combine"]').once('js-event').on('change', function() {
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
    var mediaCount = 'image not available';

    if (product.attr('gtm-dimension4') && product.attr('gtm-dimension4') !== 'image not available') {
      mediaCount = parseInt(product.attr('gtm-dimension4'));
    }

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
      'dimension4': mediaCount,
      'dimension5': product.attr('gtm-sku-type'),
      'metric1': product.attr('gtm-cart-value')
    };

    if (product.attr('gtm-path-trace')) {
      productData.dimension8 = product.attr('gtm-path-trace');
    }

    return productData;
  };

  /**
   * Helper function to push customer type to GTM.
   *
   * @param customerType
   */
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
      }
    };

    dataLayer.push(data);
  };

  /**
   * Helper function to push checkout option to GTM.
   *
   * @param optionLabel
   * @param step
   */
  Drupal.alshaya_seo_gtm_push_checkout_option = function(optionLabel, step) {
    var data = {
      'event': 'checkoutOption',
      'ecommerce': {
        'checkout_option': {
          'actionField': {
            'step':step,
            'option': optionLabel
          }
        }
      }
    };

    dataLayer.push(data);
  };

  /**
   * Helper function to push product impressions to GTM.
   *
   * @param currencyCode
   * @param impressions
   */
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

  /**
   * Helper function to push promotion impressions to GTM.
   *
   * @param highlights
   * @param gtmPageType
   * @param event
   */
  Drupal.alshaya_seo_gtm_push_promotion_impressions = function(highlights, gtmPageType, event) {
    var promotions = [];

    highlights.each(function(key, highlight) {
      var creative = '';
      if (gtmPageType === 'Top Navigation') {
        creative = Drupal.url($(highlight).find('.field--name-field-highlight-image img').attr('src'));
      }
      else {
        creative = Drupal.url($(highlight).find('.field--name-field-banner img').attr('src'));
      }

      var creativeParts = creative.split('/');
      var fileName = creativeParts[creativeParts.length - 1];
      //Strip off any query parameters.
      if (fileName.indexOf('?') !== -1) {
        fileName = fileName.substring(0, fileName.indexOf('?'));
      }

      // Remove file extensions from fileName.
      if (fileName.lastIndexOf('.') !== -1) {
        fileName = fileName.substring(0, fileName.lastIndexOf('.'));
      }

      var promotion = {
        'id': fileName,
        'name': gtmPageType,
        'creative': creative,
        'position': 'slot' + parseInt(key)+1
      };

      if (event === 'click') {
        if (gtmPageType !== 'Top Navigation') {
          promotion.position = 'slot' + parseInt($('.paragraph--type--promo-block').index($(highlight))) + 1;
        }
        else {
          promotion.position = 'slot' + parseInt($(highlight).closest('highlights').find('[gtm-type="gtm-highlights"]').index($(highlight))) + 1;
        }
      }

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
      data = {
        'event': event,
        'ecommerce': {
          'promoClick': {
            'promotions': promotions
          }
        }
      };

    }

    dataLayer.push(data);
  };

  /**
   * Helper function to push Product click events to GTM.
   *
   * @param element
   * @param currencyCode
   * @param listName
   */
  Drupal.alshaya_seo_gtm_push_product_clicks = function(element, currencyCode, listName) {
    var product = Drupal.alshaya_seo_gtm_get_product_values(element);
    product.variant = '';
    var data = {
      'event': 'productClick',
      'ecommerce': {
        'currencyCode': currencyCode,
        'click': {
          'actionField': listName,
          'products': [product]
        }
      }
    };

    dataLayer.push(data);
  };

  /**
   * Helper function to push virtual checkout options.
   */
  Drupal.alshaya_seo_gtm_push_virtual_checkout_option = function() {
    var data = {
      'event':'VirtualPageview',
      'virtualPageURL':'/virtualpv/checkout/subdelivery',
      'virtualPageTitle' : 'Checkout Sub-Delivery'
    };

    dataLayer.push(data);
  };

  /**
   * Helper function to push lead events.
   *
   * @param leadType
   */
  Drupal.alshaya_seo_gtm_push_lead_type = function(leadType) {
    dataLayer.push({'event' : 'leads', 'leadType' : leadType});
  };

  /**
   * Helper funciton to push sign-in type event.
   *
   * @param signinType
   */
  Drupal.alshaya_seo_gtm_push_signin_type = function(signinType) {
    dataLayer.push({'event' : 'User Login & Register', 'signinType' : signinType});
  };

})(jQuery, Drupal, dataLayer);
