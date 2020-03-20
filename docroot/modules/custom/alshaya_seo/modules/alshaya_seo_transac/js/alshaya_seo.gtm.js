/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  var mouseenterTime = 0;
  var gtm_execute_onetime_events = true;
  var currentListName = null;

  Drupal.behaviors.seoGoogleTagManager = {
    attach: function (context, settings) {
      $('.sku-base-form').once('alshaya-seo-gtm').on('variant-selected', function (event, variant, code) {
        var product = $(this).closest('article[gtm-type="gtm-product-link"]');
        var sku = $(this).attr('data-sku');
        var productKey = (product.attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';
        if (typeof drupalSettings[productKey][sku] === 'undefined') {
          return;
        }

        var variantInfo = drupalSettings[productKey][sku]['variants'][variant];

        product.attr('gtm-product-sku', variant);
        product.attr('gtm-price', variantInfo['gtm_price']);
      });

      // For simple grouped products.
      $('article.entity--type-node').once('alshaya-seo-gtm-simple-grouped').on('group-item-selected', function (event, variant) {
        var sku = $(this).attr('data-sku');
        var productKey = ($(this).attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';
        if (typeof drupalSettings[productKey][sku] === 'undefined') {
          return;
        }

        var variantInfo = drupalSettings[productKey][sku]['group'][variant];

        $(this).attr('gtm-main-sku', variant);
        $(this).attr('gtm-product-sku', variant);
        $(this).attr('gtm-price', variantInfo['gtm_price']);
      });

      $('.sku-base-form').once('js-event').on('product-add-to-cart-success', function () {
        var addedProduct = $(this).closest('article[gtm-type="gtm-product-link"]');
        var quantity = parseInt($('.form-item-quantity select', $(this)).val());
        var size = $('.form-item-configurables-size select option:selected', $(this)).text();
        var selectedVariant = '';

        if (addedProduct.attr('gtm-sku-type') === 'configurable') {
          selectedVariant = $('.selected-variant-sku', $(this)).val();
        }

        var product = Drupal.alshaya_seo_gtm_get_product_values(addedProduct);

        // Remove product position: Not needed while adding to cart.
        delete product.position;

        // Set product quantity to selected quatity.
        product.quantity = !isNaN(quantity) ? quantity : 1;

        // Set product size to selected size.
        if (!$.inArray('dimension6', drupalSettings.gtm.disabled_vars) && product.dimension2 !== 'simple') {
          var currentLangCode = drupalSettings.path.currentLanguage;
          if ((currentLangCode !== 'en') && (typeof size !== 'undefined')) {
            size = drupalSettings.alshaya_product_size_config[size];
          }
          if (product.hasOwnProperty('dimension6') && product.dimension6) {
            product.dimension6 = size;
          }
        }

        // Set product variant to the selected variant.
        if (product.dimension2 !== 'simple') {
          product.variant = selectedVariant;
        }
        else {
          product.variant = product.id;
        }

        // Calculate metric 1 value.
        product.metric2 = product.price * product.quantity;

        var productData = {
          event: 'addToCart',
          ecommerce: {
            currencyCode: drupalSettings.currency_code,
            add: {
              products: [
                product
              ]
            }
          }
        };

        dataLayer.push(productData);
      });

      // Push GTM event on add to cart failure.
      $('.sku-base-form').once('js-event-fail').on('product-add-to-cart-failed', function () {
        var sku = $(this).closest('article[gtm-type="gtm-product-link"]').attr('gtm-main-sku');
        var errorMessage = $('.errors-container .error .message', $(this)).text();
        // Get selected attributes.
        var attributes = [];
        $('#configurable_ajax select', $(this)).each(function() {
          var configLabel = $(this).attr('data-default-title');
          var configValue = $('option:selected', $(this)).text();
          var attribute = configLabel + ': ' + configValue;
          attributes.push(attribute);
        });
        // Set Event label.
        var label = 'Update cart failed for Product [' + sku + '] ';
        label = label + attributes.join(', ');
        var productData = {
          event: 'eventTracker',
          eventCategory: 'Update cart error',
          eventAction: errorMessage,
          eventLabel: label,
          eventValue: 0,
          nonInteraction: 0,
        };
        dataLayer.push(productData);
      });

      // Global variables & selectors.
      var impressions = [];
      var body = $('body');
      var currencyCode = body.attr('gtm-currency');
      var gtmPageType = body.attr('gtm-container');
      var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
      var productLinkProcessedSelector = $('.impression-processed[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
      var listName = body.attr('gtm-list-name');
      var removeCartSelector = $('a[gtm-type="gtm-remove-cart"]', context);
      var cartCheckoutLoginSelector = $('body[gtm-container="checkout login page"]');
      var cartCheckoutDeliverySelector = $('body[gtm-container="checkout delivery page"]');
      var cartCheckoutPaymentSelector = $('body[gtm-container="checkout payment page"]');
      var cartPage = $('body[gtm-container="cart page"]');
      var orderConfirmationPage = $('body[gtm-container="purchase confirmation page"]');
      var subDeliveryOptionSelector = $('#shipping_methods_wrapper .shipping-methods-container', context);
      var topNavLevelOneSelector = $('li.menu--one__list-item', context);
      var couponCode = $('form.customer-cart-form', context).find('input#edit-coupon').val();
      var storeFinderFormSelector = $('form#views-exposed-form-stores-finder-page-1');
      var isCCPage = false;
      var isPaymentPage = false;
      var isSearchPage = false;
      var isRegistrationSuccessPage = false;
      var isStoreFinderPage = false;
      var originalCartQty = 0;
      var updatedCartQty = 0;
      var subListName = '';
      var leadType = '';
      var promotionImpressionSubnavFired = false;
      var ccPaymentsClicked = false;
      var footerNewsletterSubmiClicked = false;
      var deliveryType = 'Home Delivery';
      var userDetails = '';
      var userId = '';
      if (typeof drupalSettings.userDetails === 'undefined') {
        userDetails = drupalSettings.user;
        userId = userDetails.uid;
      }
      // userDetails is set in case of google/facebook login.
      else  {
        userDetails = drupalSettings.userDetails;
        userId = userDetails.userID;
      }

      if (localStorage.getItem('userID') === undefined) {
        localStorage.setItem('userID', userId);
      }

      // Set platformType.
      $('body').once('page-load-gta').each(function () {
        var md = new MobileDetect(window.navigator.userAgent);

        if (md.tablet() !== null) {
            userDetails.platformType = 'tablet';
        }
        else if (md.mobile()) {
            userDetails.platformType = 'mobile';
        }
        else {
            userDetails.platformType = 'desktop';
        }

        // For checkout pages, privilegeCustomer is added in checkout step.
        if (cartPage.length !== 0 ||
            cartCheckoutLoginSelector.length !== 0 ||
            cartCheckoutDeliverySelector.length !== 0 ||
            cartCheckoutPaymentSelector.length !==0) {
          delete userDetails.privilegeCustomer;
        }

        // Push on all pages except confirmation page.
        if (orderConfirmationPage.length === 0) {
          dataLayer.push(userDetails);
        }

          if ($(context).filter('article[data-vmode="modal"]').length === 1
            || $(document).find('article[data-vmode="full"]').length === 1) {

          if ($(document).find('article[data-vmode="full"]').length === 1) {
            var productContext = $(document).find('article[data-vmode="full"]');
          }
          else {
            var productContext = $(context).filter('article[data-vmode="modal"]');
          }

          var product = Drupal.alshaya_seo_gtm_get_product_values(productContext);
          product.variant = '';
          if (currentListName != null && currentListName !== 'PDP-placeholder') {
            product.list = currentListName;
            currentListName = null;
          }
          var data = {
            event: 'productDetailView',
            ecommerce: {
              currencyCode: currencyCode,
              detail: {
                products: [product]
              }
            }
          };

          dataLayer.push(data);
        }
      });

      // If we receive an empty page type, set page type as not defined.
      if (gtmPageType === 'not defined') {
        if (document.location.pathname.startsWith('/' + drupalSettings.path.currentLanguage + '/user')) {
          var currPath = document.location.pathname;
          var pagePath = currPath.replace('/' + drupalSettings.path.currentLanguage + '/user/', '');
          var gtmPageTypeArray = pagePath.split('/');
          for (var i = 0; i < gtmPageTypeArray.length; i++) {
            if (gtmPageTypeArray[i] == parseInt(gtmPageTypeArray[i])) {
              gtmPageTypeArray.splice(i, 1);
            }
          }
          var gtmPageSubType = gtmPageTypeArray.join('-');
          gtmPageType = 'myaccount-' . gtmPageSubType;
        }
        else {
          gtmPageType = document.location.pathname.split('/').join('-');
        }
      }

      // If we are on checkout page -- Click & collect method.
      if (document.location.search === '?method=cc') {
        isCCPage = true;
      }

      // If we are on payment page.
      if (document.location.pathname === Drupal.url('cart/checkout/payment')) {
        isPaymentPage = true;
      }

      if (document.location.pathname === Drupal.url('user/register/complete')) {
        isRegistrationSuccessPage = true;
      }

      if (document.location.pathname === Drupal.url('store-finder/list')) {
        isStoreFinderPage = true;
      }

      if (document.location.pathname === Drupal.url('search')) {
        isSearchPage = true;
      }

      if (isSearchPage) {
        $('.c-header #edit-keywords').once('internalsearch').each(function () {
          var keyword = Drupal.getQueryVariable('keywords');
          var noOfResult = parseInt($('.view-header', context).text().replace(Drupal.t('items'), '').trim());
          noOfResult = isNaN(noOfResult) ? 0 : noOfResult;

          var action = noOfResult === 0 ? '404 Results' : 'Successful Search';
          var interaction = noOfResult === 0 ? noOfResult : 1;

          dataLayer.push({
            event: 'eventTracker',
            eventCategory: 'Internal Site Search',
            eventAction: action,
            eventLabel: keyword,
            eventValue: noOfResult,
            nonInteraction: interaction,
          });
        });
      }

      if ((isRegistrationSuccessPage) && (context === document)) {
        Drupal.alshaya_seo_gtm_push_signin_type('Registration Success');
      }

      // Cookie based events, only to be processed once on page load.
      $(document).once('gtm-onetime').each(function () {
        // Check if social login window opened to avoid GTM push from
        // social login window.
        var socialWindow = false;
        if(window.name == 'ConnectWithSocialAuth'){
          var socialWindow = true;
        }

        // Fire sign-in success event on successful sign-in from parent window.
        if (!(socialWindow) && userDetails.userID !== undefined && userDetails.userID !== 0 && localStorage.getItem('userID') !== userDetails.userID) {
          Drupal.alshaya_seo_gtm_push_signin_type('Login Success');
          localStorage.setItem('userID', userDetails.userID);
        }

        // Fire logout success event on successful sign-in.
        if (localStorage.getItem('userID') && localStorage.getItem('userID') != userDetails.userID && userDetails.userID === 0) {
          Drupal.alshaya_seo_gtm_push_signin_type('Logout Success');
          localStorage.setItem('userID', userDetails.userID);
        }

        // Fire lead tracking on registration success/ user update.
        if (drupalSettings.alshaya_gtm_create_user_lead !== undefined &&
            drupalSettings.alshaya_gtm_create_user_pagename !== undefined) {
          var leadType = drupalSettings.alshaya_gtm_create_user_pagename;
          if (leadType) {
            dataLayer.push({
              event: 'leads',
              leadType: leadType
            });
          }
        }
        else if ($.cookie('Drupal.visitor.alshaya_gtm_update_user_lead') !== undefined) {
          dataLayer.push({
            event: 'leads',
            leadType: 'my account'
          });

          $.removeCookie('Drupal.visitor.alshaya_gtm_update_user_lead', {path: '/'});
        }

        var pcRegistration = drupalSettings.alshaya_gtm_create_user_pc;

        if (pcRegistration !== undefined && pcRegistration !== '6362544') {
          dataLayer.push({
            event: 'pcMember',
            pcType: 'pc club member'
          });
        }
      });

      /**
       * Track coupon code application.
       */
      if (couponCode) {
        var cart = $.cookie('Drupal.visitor.acq_cart_id');
        var appliedCoupon = $.cookie('coupon_applied');
        if (cart + '|' + couponCode !== appliedCoupon) {
          var couponError = $('.form-item-coupon').find('.form-item--error-message').text();
          var status = '';
          if (couponError !== '') {
            status = 'fail';
          }
          else {
            status = 'pass';
          }

          dataLayer.push({
            event: 'promoCode',
            couponCode: couponCode,
            couponStatus: status
          });

          $.cookie('coupon_applied', cart + '|' + couponCode);
        }
      }
      else if (gtmPageType === 'cart page') {
        $.removeCookie('coupon_applied');
      }

      /**
       * Track store finder clicks.
       */
      if (isStoreFinderPage && gtm_execute_onetime_events) {
        var searchTextBox = storeFinderFormSelector.find('input[data-drupal-selector="edit-geolocation-geocoder-google-places-api"]');
        var keyword = searchTextBox.val();
        if (keyword !== '') {
          var resultCount = $('[data-drupal-selector^="views-form-stores-finder-page-1"]', context).find('.list-view-locator').length;
          if (context === document) {
            Drupal.alshaya_seo_gtm_push_store_finder_search(keyword, 'header', resultCount);
          }
          else if ((context !== document) &&
            ($('input#edit-geolocation-geocoder-google-places-api', context).length === 0)) {
            Drupal.alshaya_seo_gtm_push_store_finder_search(keyword, 'header', resultCount);
          }
        }
      }

      // CheckoutOption event for delivery page.
      $('[data-drupal-selector="edit-actions-get-shipping-methods"]').once('js-event').on('mousedown', function () {
        Drupal.alshaya_seo_gtm_push_checkout_option(deliveryType, 2);
      });

      $('div.address--deliver-to-this-address.address--controls a').once('js-event').on('click', function () {
        Drupal.alshaya_seo_gtm_push_checkout_option('Home Delivery', 2);
      });

      $('button.delivery-home-next, [data-drupal-selector="edit-member-delivery-home-address-form-save"]').once('js-event').on('mousedown', function () {
        if (gtmPageType == 'checkout delivery page') {
          Drupal.alshaya_seo_gtm_push_checkout_option('Home Delivery - subdelivery ', 2);
        }
      });

      $('[data-drupal-selector="edit-actions-ccnext"]').once('js-event').on('mousedown', function () {
        ccPaymentsClicked = true;
        Drupal.alshaya_seo_gtm_push_checkout_option('Click & Collect', 2);
      });

      // Trigger deliveryOption event for click & collect tab click.
      cartCheckoutDeliverySelector.find('div[gtm-type="checkout-click-collect"] > a').once('delivery-option-event').on('click', function() {
        dataLayer.push({event: 'deliveryOption', eventLabel: 'Click & Collect'});
      });

      if (isCCPage && gtm_execute_onetime_events && !ccPaymentsClicked) {
        if ($('li.select-store', context).length > 0) {
          var keyword = $('input#edit-store-location').val();
          var resultCount = $('li.select-store', context).length;
          Drupal.alshaya_seo_gtm_push_store_finder_search(keyword, 'checkout', resultCount);
        }
      }

      /**
       * Newsletter tracking GTM.
       */
      $('footer .edit-newsletter').click(function () {
        footerNewsletterSubmiClicked = true;
      });

      // Trigger GTM push event on AJAX completion of add to cart button.
      $(document).once('js-event').ajaxComplete(function (event, xhr, settings) {
        gtm_execute_onetime_events = true;
        if ((settings.hasOwnProperty('extraData')) && (settings.extraData.hasOwnProperty('_triggering_element_value')) && (settings.extraData._triggering_element_value.toLowerCase() === Drupal.t('sign up').toLowerCase())) {
          var responseJSON = xhr.responseJSON;
          var responseMessage = '';
          $.each(responseJSON, function (key, obj) {
            if (obj.method === 'newsletterHandleResponse') {
              responseMessage = obj.args[0].message;
              return false;
            }
          });

          if ((responseMessage === 'success') && (footerNewsletterSubmiClicked)) {
            Drupal.alshaya_seo_gtm_push_lead_type('footer');
          }
        }
      });

      /**
       * Quantity update in cart.
       */
      // Trigger removeFromCart & addToCart events based on the quantity update on cart page.
      $('select[gtm-type="gtm-quantity"]').on('select2:open', function () {
        originalCartQty = $(this).val();
      }).once('js-event').on('change', function () {
        if (originalCartQty !== 0) {
          updatedCartQty = $(this).val();
          var diffQty = updatedCartQty - originalCartQty;
          var cartItem = $(this).closest('td.quantity').siblings('td.name').find('[gtm-type="gtm-remove-cart-wrapper"]');
          var product = Drupal.alshaya_seo_gtm_get_product_values(cartItem);
          var event = '';

          // Set updated product quantity.
          product.quantity = Math.abs(diffQty);

          // Set item's size as dimension6.
          if (!$.inArray('dimension6', settings.gtm.disabled_vars) && cartItem.attr('gtm-size')) {
            product.dimension6 = cartItem.attr('gtm-size');
          }

          // Remove product position: Not needed while updating item in cart.
          delete product.position;

          product.metric2 = product.quantity * product.price;

          if (diffQty < 0) {
            event = 'removeFromCart';
            product.metric2 = -1 * product.metric2;
          }
          else {
            event = 'addToCart';
          }

          var data = {
            event: event,
            ecommerce: {
              currencyCode: currencyCode
            }
          };

          if (event === 'removeFromCart') {
            // Delete list from cookie.
            var listValues = {};
            if ($.cookie('product-list') !== undefined) {
              listValues = JSON.parse($.cookie('product-list'));
            }
            delete listValues[product.id];
            $.cookie('product-list', JSON.stringify(listValues), {path: '/'});
            data.ecommerce.remove = {
              products: [
                product
              ]
            };
          }
          else if (event === 'addToCart') {
            data.ecommerce.add = {
              products: [
                product
              ]
            };
          }

          dataLayer.push(data);
        }
      });

      /**
       * Remove Product from cart.
       */
      // Add click handler to fire 'removeFromCart' event to GTM.
      removeCartSelector.once('js-event').each(function () {
        // Get selector holding details around the product.
        var removeItem = $(this).closest('td.quantity').siblings('td.name').find('[gtm-type="gtm-remove-cart-wrapper"]');

        removeItem.on('cart-item-removed', function () {
          var product = Drupal.alshaya_seo_gtm_get_product_values(removeItem);

          // Set product quantity to the number of items selected for quantity.
          product.quantity = parseInt($(this).closest('tr').find('td.quantity select').val());

          // Set selected size as dimension6.
          if (!$.inArray('dimension6', settings.gtm.disabled_vars) && removeItem.attr('gtm-size')) {
            product.dimension6 = removeItem.attr('gtm-size');
          }

          // Remove product position: Not needed while removing item from cart.
          delete product.position;

          product.metric2 = -1 * product.quantity * product.price;

          var data = {
            event: 'removeFromCart',
            ecommerce: {
              currencyCode: currencyCode,
              remove: {
                products: [
                  product
                ]
              }
            }
          };

          dataLayer.push(data);
        });
      });

      /**
       * Tracking Home Delivery.
       */
      if (cartCheckoutDeliverySelector.length !== 0) {
        // Fire checkout option event if home delivery option is selected by default on delivery page.
        if (subDeliveryOptionSelector.find('.form-type-radio').length === 0
          && cartCheckoutDeliverySelector.find('div[gtm-type="checkout-home-delivery"]').once('js-event').hasClass('active--tab--head')
        ) {
          deliveryType = 'Home Delivery';
        }

        var deliveryAddressButtons = [
          cartCheckoutDeliverySelector.find('.address--deliver-to-this-address > a'),
          cartCheckoutDeliverySelector.find('#add-address-button'),
        ];

        $(deliveryAddressButtons)
          .each(function() {
            $(this).once('delivery-address').on('click, mousedown', function (e) {
              let eventLabel = $(this).attr('id') === 'add-address-button' ? 'add new address' : 'deliver to this address';
              dataLayer.push({event: 'deliveryAddress', eventLabel: eventLabel});
            });
          });
      }

      // Trigger deliveryOption event for "home delivery" tab click.
      $('body[gtm-container="checkout click and collect page"], body[gtm-container="checkout delivery page"]')
        .find('div[gtm-type="checkout-home-delivery"]:not(.active--tab--head) > a')
        .once('delivery-option-event')
        .on('click', function() {
          dataLayer.push({event: 'deliveryOption', eventLabel: 'Home Delivery'});
        });

      /**
       * GTM virtual page tracking for click & collect journey.
       */
      if (isCCPage) {
        if ($('#store-finder-wrapper', context).length > 0) {
          if (!(body.hasClass('virtualpageview-fired'))) {
            dataLayer.push({
              event: 'VirtualPageview',
              virtualPageURL: '/virtualpv/click-and-collect/step1/click-and-collect-view',
              virtualPageTitle: 'C&C Step 1 – Click and Collect View'
            });
            body.addClass('virtualpageview-fired');
          }

        }

        $('.store-actions a.select-store', context).once('js-event').click(function () {
          let selectedStore = $(this).parent('.store-actions');
          dataLayer.push({
            event: 'VirtualPageview',
            virtualPageURL: ' /virtualpv/click-and-collect/step2/select-store',
            virtualPageTitle: 'C&C Step 2 – Select Store'
          },
          {
            event: 'storeSelect',
            storeName: selectedStore.attr('gtm-store-title').replace(/\s+/g,' ').replace(/^\s+|\s+$/g, ''),
            storeAddress: selectedStore.attr('gtm-store-address').replace(/\s+/g,' ').replace(/^\s+|\s+$/g, ''),
          });
        });
      }

      /**
       * Tracking selected payment option.
       */
      // Fire this only if on checkout Payment option page & Ajax response brings in cart-checkout-payment div.
      if ((cartCheckoutPaymentSelector.length !== 0) && ($('fieldset[gtm-type="cart-checkout-payment"]', context).length > 0)) {
        var preselectedMethod = $('[gtm-type="cart-checkout-payment"] input:checked');
        if (preselectedMethod.length === 1) {
          var preselectedMethodLabel = preselectedMethod.siblings('label').find('.method-title').text();
          if (drupalSettings.path.currentLanguage === 'ar') {
            preselectedMethodLabel = drupalSettings.alshaya_payment_options_translations[preselectedMethodLabel];
          }
          $('[data-drupal-selector="edit-actions-next"]').once('js-event').on('mousedown', function () {
            Drupal.alshaya_seo_gtm_push_checkout_option(preselectedMethodLabel, 3);
          });
        }
      }

      // Push purchaseSuccess for Order confirmation page.
      if (orderConfirmationPage.length !== 0 && settings.gtmOrderConfirmation) {
        dataLayer.push({
          event: settings.gtmOrderConfirmation.event,
          virtualPageUrl: settings.gtmOrderConfirmation.virtualPageURL,
          virtualPageTitle: settings.gtmOrderConfirmation.virtualPageTitle
        });
      }

      /**
       * Product Click Handler.
       */
      // Add click link handler to fire 'productClick' event to GTM.
      productLinkSelector.each(function () {
        $(this).once('js-event').on('click', function (e) {
          var that = $(this);
          var position = $('.views-infinite-scroll-content-wrapper .c-products__item').index(that.closest('.c-products__item')) + 1;

          currentListName = listName;
          Drupal.alshaya_seo_gtm_push_product_clicks(that, currencyCode, listName, position);
        });
      });

      /**
       * Product click handler for Modals.
       */
      // Add click link handler to fire 'productClick' event to GTM.
      $('a[href*="product-quick-view"]').each(function () {
        $(this).once('js-event').on('click', function (e) {
          var that = $(this).closest('article[data-vmode="teaser"]');
          var position = '';
          if (listName.indexOf('placeholder') > -1) {
            if (that.closest('.horizontal-crossell').length > 0) {
              subListName = listName.replace('placeholder', 'CS');
            }
            else if (that.closest('.horizontal-upell').length > 0) {
              subListName = listName.replace('placeholder', 'US');
            }
            else if (that.closest('.horizontal-related').length > 0) {
              subListName = listName.replace('placeholder', 'RELATED');
            }
          }

          currentListName = subListName;
          position = drupalSettings.impressions_position[that.attr('data-nid') + '-' + subListName];
          Drupal.alshaya_seo_gtm_push_product_clicks(that, currencyCode, subListName, position);
        });
      });

      var highlightsPosition = 1;
      topNavLevelOneSelector.once('set-positions').find('.highlights [gtm-type="gtm-highlights"]').each(function () {
        $(this).data('position', highlightsPosition);
        highlightsPosition++;
      });

      /**
       * Tracking internal promotion impressions.
       */
      // Tracking menu level promotions.
      topNavLevelOneSelector.each(function () {
        $(this).once('js-event').mouseenter(function () {
          mouseenterTime = (new Date()).getTime();
        }).mouseleave(function () {
          var mouseOverTime = (new Date()).getTime() - mouseenterTime;
          if ((mouseOverTime >= 2000) && ($(this).hasClass('has-child'))) {
            var highlights = $(this).find('.highlights [gtm-type="gtm-highlights"]');

            if ((highlights.length > 0) && (!promotionImpressionSubnavFired)) {
              promotionImpressionSubnavFired = true;
              Drupal.alshaya_seo_gtm_push_promotion_impressions(highlights, 'Top Navigation', 'promotionImpression');
            }
          }
        });
      });

      $('.sub-nav-link').click(function () {
        var parent = $(this).closest('ul.menu--two__list');
        if (parent.length !== 0) {
          var highlights = parent.find('.highlights [gtm-type="gtm-highlights"]');
          if ((highlights.length > 0) && (!promotionImpressionSubnavFired)) {
            promotionImpressionSubnavFired = true;
            Drupal.alshaya_seo_gtm_push_promotion_impressions(highlights, 'Top Navigation', 'promotionImpression');
          }
        }
      });

      $('[gtm-type="gtm-highlights"]').once('js-event').on('click', function () {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), 'Top Navigation', 'promotionClick');
      });

      // If both promo block and body field images exist make sure promotionImpression is fired only once.
      // Adding slider promo banner class to track.
      if ($('.paragraph--type--promo-block, .c-slider-promo').length > 0 && $('.field--name-body').length > 0 && (context === document)) {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($('.paragraph--type--promo-block, .c-slider-promo, .field--name-body, .field--name-body > div[class^="rectangle"]:visible'), gtmPageType, 'promotionImpression');
      }

      else if ($('.paragraph--type--promo-block, .c-slider-promo').length > 0 && (context === document)) {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($('.paragraph--type--promo-block, .c-slider-promo'), gtmPageType, 'promotionImpression');
        }

        // Tracking promotion image view inside body field.
      else if ($('.field--name-body').length > 0 && (context === document)) {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($('.field--name-body'), gtmPageType, 'promotionImpression');
        }

      // Tracking of homepage banner.
      $('.c-content__slider .field--name-field-banner').once('js-event').on('click', function () {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), gtmPageType, 'promotionClick');
      });

      // Tracking view of homepage banner in body.
      $('.field--name-body, .paragraph--type--promo-block, .paragraph--type--banner').once('js-event').on('click', function () {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), gtmPageType, 'promotionClick');
      });


      // Tracking promotion banner on PLP.
      if (listName === 'PLP') {
        if ($('.views-field-field-promotion-banner').length > 0 && (context === document)) {
          if ($(this).find('a').length > 0) {
            Drupal.alshaya_seo_gtm_push_promotion_impressions($('.views-field-field-promotion-banner'), 'PLP', 'promotionImpression');
          }
        }

        // Tracking click on promo banner PLP.
        $('.views-field-field-promotion-banner').once('js-event').each(function () {
          if ($(this).find('a').length > 0) {
            $(this).on('click', function () {
              Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), 'PLP', 'promotionClick');
            });
          }
        });
      }

      /**
       * Tracking clicks on fitler & sort options.
       */
      if (listName !== undefined) {
        if (listName.includes('PLP') || listName === 'Search Results Page') {
          var section = listName;
          if (listName.includes('PLP')) {
            section = $('h1.c-page-title').text().toLowerCase();
          }

          // Track facet filters.
          $('li.facet-item').once('js-event').on('click', function () {
            var selectedVal = $(this).find('a').attr('data-drupal-facet-item-label');
            var facetTitle = $(this).find('a').attr('data-drupal-facet-label');
            var data = {
              event: 'filter',
              siteSection: section.trim(),
              filterType: facetTitle,
              filterValue: selectedVal.trim(),
            };

            dataLayer.push(data);
          });

          // Track sorts.
          $('input[name="sort_bef_combine"]', context).once('js-event').on('change', function () {
            var sortValue = $("label[for='" + $(this).attr('id') + "']").first().text();
            sortValue.trim();
            var data = {
              event: 'sort',
              siteSection: section.trim(),
              sortValue: sortValue
            };

            dataLayer.push(data);
          });
        }
      }

      gtm_execute_onetime_events = false;
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
      name: product.attr('gtm-name'),
      id: product.attr('gtm-main-sku'),
      price: parseFloat(product.attr('gtm-price')),
      category: product.attr('gtm-category'),
      variant: product.attr('gtm-product-sku'),
      dimension2: product.attr('gtm-sku-type'),
      dimension3: product.attr('gtm-dimension3'),
      dimension4: mediaCount
    };

    if (product.attr('gtm-brand')) {
      productData.brand = product.attr('gtm-brand');
    }

    if (product.attr('gtm-dimension1')) {
      productData.dimension1 = product.attr('gtm-dimension1');
    }

    if (product.attr('gtm-dimension5')) {
      productData.dimension5 = product.attr('gtm-dimension5');
    }

    // If list variable is set in cookie, retrieve it.
    if ($.cookie('product-list') !== undefined) {
      var listValues = JSON.parse($.cookie('product-list'));
      if (listValues[productData.id]) {
        productData.list = listValues[productData.id]
      }
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
      event: 'checkoutOption',
      ecommerce: {
        checkout_option: {
          actionField: {
            step: 1,
            option: customerType
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
  Drupal.alshaya_seo_gtm_push_checkout_option = function (optionLabel, step) {
    var data = {
      event: 'checkoutOption',
      ecommerce: {
        checkout_option: {
          actionField: {
            step: step,
            option: optionLabel
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
  Drupal.alshaya_seo_gtm_push_impressions = function (currencyCode, impressions) {
    // To avoid max size in POST data issue we do it in batches of 10.
    while (impressions.length > 0) {
      var data = {
        event: 'productImpression',
        ecommerce: {
          currencyCode: currencyCode,
          impressions: impressions.splice(0, 10)
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
  Drupal.alshaya_seo_gtm_push_promotion_impressions = function (highlights, gtmPageType, event) {
    var promotions = [];
    var promo_para_elements = '.paragraph--type--promo-block, .c-slider-promo, .field--name-body > div[class^="rectangle"]:visible';
    var promotion_counter = 0;
    highlights.each(function (key, highlight) {
      var position = 1;
      var creative = '';

      if ((gtmPageType === 'Top Navigation') &&
        ($(highlight).find('.field--name-field-highlight-image img', '.field--name-field-highlight-image picture img').attr('src') !== undefined)) {
        creative = Drupal.url($(highlight).find('.field--name-field-highlight-image img').attr('src'));
        if (!creative) {
          creative = Drupal.url($(highlight).find('.field--name-field-highlight-image picture img').attr('src'));
        }
        position = $(highlight).data('position');
      }
      else if ((gtmPageType === 'PLP') &&
        ($(highlight).find('.field-content img').attr('src') !== undefined)) {
        creative = Drupal.url($(highlight).find('.field-content img').attr('src'));
        position = 1;
      }
      // adding or condition for advanced page pageType.
      else if (gtmPageType === 'home page' || gtmPageType === 'department page' || gtmPageType === 'advanced page') {
        if ($(highlight).find(promo_para_elements).length > 0) {
          return true;
        }
        if ($(highlight).find('img').is(':visible')) {
          var imgElem = $(highlight).find('picture:first img');
          if (imgElem.length === 0) {
            imgElem = $(highlight).find('img:first');
          }

          if (imgElem.length === 0) {
            return true;
          }

          var imgSrc = (typeof imgElem.attr('data-src') === 'undefined') ?
            imgElem.attr('src') :
            imgElem.attr('data-src');

          // add position value only if image is there.
          if (typeof imgSrc !== 'undefined') {
            position = promotion_counter;
            if (event === 'promotionClick') {
              position = imgElem.data('position');
            } else {
              // Skip already processed images.
              if (typeof imgElem.data('position') !== 'undefined' || imgElem.data('position') > -1) {
                return true;
              }

              imgElem.data('position', promotion_counter);
            }
            creative = Drupal.url(imgSrc);
          }
        }
      }
      else if ($(highlight).find('.field--name-field-banner img', '.field--name-field-banner picture img').attr('src') !== undefined) {
        creative = Drupal.url($(highlight).find('.field--name-field-banner img').attr('src'));
        if (!creative) {
          creative = Drupal.url($(highlight).find('.field--name-field-banner picture img').attr('src'));
        }
        position = parseInt($('.paragraph--type--promo-block').index($(highlight))) + 1;
      }

      if (creative) {
        var creativeParts = creative.split('/');
        var fileName = creativeParts[creativeParts.length - 1];
        // Strip off any query parameters.
        if (fileName.indexOf('?') !== -1) {
          fileName = fileName.substring(0, fileName.indexOf('?'));
        }

        // Remove file extensions from fileName.
        if (fileName.lastIndexOf('.') !== -1) {
          fileName = fileName.substring(0, fileName.lastIndexOf('.'));
        }
        fileName = fileName.toLowerCase();

        if ((fileName !== undefined) && (fileName !== '') && (
          (fileName.indexOf('hp') === 0) ||
          (fileName.indexOf('mm') === 0) ||
          (fileName.indexOf('dp') === 0) ||
          (fileName.indexOf('lp') === 0) ||
          (fileName.indexOf('oth') === 0)
        )) {
          var promotion = {
            creative: creative.replace(/\/en\/|\/ar\//, ''),
            id: fileName,
            name: gtmPageType,
            position: 'slot' + position
          };
          if (typeof promotion !== 'undefined') {
            promotion_counter++;
            promotions.push(promotion);
          }
        }
      }
    });

    if (promotions.length > 0) {
      if (event === 'promotionClick') {
        // We don't want to trigger impressions again after click.
        mouseenterTime = (new Date()).getTime() + 1000000000;

        var data = {
          event: event,
          ecommerce: {
            promoClick: {
              promotions: promotions
            }
          }
        };

        if (gtmPageType === 'Top Navigation') {
          dataLayer.push({
            event: 'promotionImpression',
            ecommerce: {
              promoView: {
                promotions: promotions
              }
            }
          });
        }
      }
      else {
        var data = {
          event: 'promotionImpression',
          ecommerce: {
            promoView: {
              promotions: promotions
            }
          }
        };
      }

      dataLayer.push(data);
    }
  };

  /**
   * Helper function to push Product click events to GTM.
   *
   * @param element
   * @param currencyCode
   * @param listName
   * @param position
   */
  Drupal.alshaya_seo_gtm_push_product_clicks = function (element, currencyCode, listName, position) {
    // Don't trigger product click event for items in cross-sell on Mobile.
    if (((element.closest('.owl-item').length !== 0) ||
            (element.closest('.no-carousel').length == 0)) &&
        ($(window).width() < 320)) {
      return;
    }

    var product = Drupal.alshaya_seo_gtm_get_product_values(element);

    // On productClick, add list variable to cookie.
    var listValues = {};
    if ($.cookie('product-list') !== undefined) {
      listValues = JSON.parse($.cookie('product-list'));
    }
    listValues[product.id] = listName;
    $.cookie('product-list', JSON.stringify(listValues), {path: '/'});
    product.variant = '';
    if (position) {
      product.position = position;
    }

    var data = {
      event: 'productClick',
      ecommerce: {
        currencyCode: currencyCode,
        click: {
          actionField: {
            list: listName
          },
          products: [product]
        }
      }
    };

    dataLayer.push(data);
  };

  /**
   * Helper function to push lead events.
   *
   * @param leadType
   */
  Drupal.alshaya_seo_gtm_push_lead_type = function (leadType) {
    dataLayer.push({event: 'leads', leadType: leadType});
  };

  /**
   * Helper funciton to push Login & Register events.
   *
   * @param eventAction
   */
  Drupal.alshaya_seo_gtm_push_signin_type = function (eventAction) {
    dataLayer.push({
      event: 'eventTracker',
      eventCategory: 'Login & Register',
      eventAction: eventAction,
      eventValue: 0,
      nonInteraction: 0
    });
  };

  /**
   * Helper function to track store finder search.
   *
   * @param keyword
   * @param location
   * @param resultCount
   */
  Drupal.alshaya_seo_gtm_push_store_finder_search = function (keyword, location, resultCount) {
    if (keyword !== '') {
      dataLayer.push({
        event: 'findStore',
        siteSection: location,
        fsKeyword: keyword,
        fsNoOfResult: resultCount
      });
    }
  };

  /**
   * Helper function to translate non-site default lang shipping method label.
   *
   * @param selectedMethodLabel
   *
   * @returns {*}
   */
  Drupal.alshaya_seo_translate_shipping_method = function (selectedMethodLabel) {
    var currentLangCode = drupalSettings.path.currentLanguage;
    if (currentLangCode !== 'en') {
      var shipping_translations = drupalSettings.alshaya_shipping_method_translations;
      if (drupalSettings.alshaya_shipping_method_translations.hasOwnProperty(selectedMethodLabel)) {
        selectedMethodLabel = shipping_translations[selectedMethodLabel];
      }
    }

    return selectedMethodLabel;
  };

  /**
   * Helper function to push productImpression to GTM.
   *
   * @param customerType
   */
  Drupal.alshaya_seo_gtm_prepare_and_push_product_impression = function (context, settings) {
    var impressions = [];
    var body = $('body');
    var currencyCode = body.attr('gtm-currency');
    var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]:not(".impression-processed"):visible', context);
    var productLinkProcessedSelector = $('.impression-processed[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
    var listName = body.attr('gtm-list-name');
    // Send impression for each product added on page (page 1 or X).
    var count = productLinkProcessedSelector.length + 1;

    if (productLinkSelector.length > 0) {
      productLinkSelector.each(function () {
        if ($(this).isElementInViewPort(0)) {
          $(this).addClass('impression-processed');
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          impression.list = listName;
          impression.position = count;
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          impressions.push(impression);
          count++;
        }
      });
      if (impressions.length > 0) {
        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, impressions);
      }
    }
  };

  // Ajax command to push deliveryAddress Event.
  $.fn.triggerDeliveryAddress = function () {
    dataLayer.push({event: 'deliveryAddress', eventLabel: 'deliver to this address'});
  };

})(jQuery, Drupal, dataLayer);
