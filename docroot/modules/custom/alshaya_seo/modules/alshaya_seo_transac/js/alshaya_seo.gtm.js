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

      // Set platformType.
      $('body').once('page-load-gta').each(function () {

        var md = new MobileDetect(window.navigator.userAgent);
        var platformType = 'desktop';
        if (md.tablet() !== null) {
            platformType = 'tablet';
        }
        else if (md.mobile()) {
            platformType = 'mobile';
        }

        var userDetails = JSON.parse(localStorage.getItem('userDetails'));

        if ((localStorage.getItem('userDetails') === undefined ||
            localStorage.getItem('userDetails') === null ||
            drupalSettings.user.uid !== userDetails.userID ||
            $.cookie('Drupal.visitor.alshaya_gtm_user_refresh') === 1) &&
            orderConfirmationPage.length !== 0) {
          Drupal.setUserDetailsInStorage();
          $.removeCookie('Drupal.visitor.alshaya_gtm_user_refresh', {path: '/'});
          userDetails = JSON.parse(localStorage.getItem('userDetails'));
        }

        Drupal.alshaya_seo_default_datalayer_push(platformType, userDetails);

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

      // List of Pages where we need to push out list of product being rendered to GTM.
      var impressionPages = [
        'home page',
        'search result page',
        'product listing page',
        'product detail page',
        'advanced page',
        'department page',
        'promotion page'
      ];

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
        // Fire sign-in success event on successful sign-in.
        if ($.cookie('Drupal.visitor.alshaya_gtm_user_logged_in') !== undefined) {
          Drupal.alshaya_seo_gtm_push_signin_type('Login Success');
          $.removeCookie('Drupal.visitor.alshaya_gtm_user_logged_in', {path: '/'});
        }

        // Fire logout success event on successful sign-in.
        if ($.cookie('Drupal.visitor.alshaya_gtm_user_logged_out') !== undefined) {
          Drupal.alshaya_seo_gtm_push_signin_type('Logout Success');
          $.removeCookie('Drupal.visitor.alshaya_gtm_user_logged_out', {path: '/'});
        }

        // Fire lead tracking on registration success/ user update.
        if ($.cookie('Drupal.visitor.alshaya_gtm_create_user_lead') !== undefined &&
          $.cookie('Drupal.visitor.alshaya_gtm_create_user_pagename') !== undefined) {
          var leadOriginPath = $.cookie('Drupal.visitor.alshaya_gtm_create_user_pagename');

          if (leadOriginPath === Drupal.url('user/register')) {
            leadType = 'registration';
          }
          else if (leadOriginPath === Drupal.url('cart/checkout/confirmation')) {
            leadType = 'confirmation';
          }

          if (leadType) {
            dataLayer.push({
              event: 'leads',
              leadType: leadType
            });
          }

          $.removeCookie('Drupal.visitor.alshaya_gtm_create_user_lead', {path: '/'});
          $.removeCookie('Drupal.visitor.alshaya_gtm_create_user_pagename', {path: '/'});
          $.removeCookie('Drupal.visitor.alshaya_gtm_update_user_lead', {path: '/'});
        }
        else if ($.cookie('Drupal.visitor.alshaya_gtm_update_user_lead') !== undefined) {
          dataLayer.push({
            event: 'leads',
            leadType: 'my account'
          });

          $.removeCookie('Drupal.visitor.alshaya_gtm_update_user_lead', {path: '/'});
        }

        var pcRegistration = $.cookie('Drupal.visitor.alshaya_gtm_create_user_pc');

        if (pcRegistration !== undefined && pcRegistration !== '6362544') {
          dataLayer.push({
            event: 'pcMember',
            pcType: 'pc club member'
          });
        }

        $.removeCookie('Drupal.visitor.alshaya_gtm_create_user_pc', {path: '/'});
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

      if (isCCPage && gtm_execute_onetime_events && !ccPaymentsClicked) {
        if ($('li.select-store', context).length > 0) {
          var keyword = $('input#edit-store-location').val();
          var resultCount = $('li.select-store', context).length;
          Drupal.alshaya_seo_gtm_push_store_finder_search(keyword, 'checkout', resultCount);
        }
      }

      /**
       * Impressions tracking on listing pages with Products.
       */
      if ((gtmPageType === 'product detail page') || (gtmPageType === 'cart page')) {
        var count_pdp_items = 1;
        if (!drupalSettings.hasOwnProperty('impressions_position')) {
          drupalSettings.impressions_position = [];
        }

        productLinkSelector.each(function () {
          // Fetch attributes for this product.
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';

          var pdpListName = '';
          var upSellCrossSellSelector = $(this).closest('.view-product-slider').parent('.views-element-container').parent();
          if (!$(this).closest('.owl-item').hasClass('cloned') && !upSellCrossSellSelector.hasClass('mobile-only-block')) {
            // Check whether the product is in US or CS region & update list accordingly.
            if (listName.includes('placeholder')) {
              if (upSellCrossSellSelector.hasClass('horizontal-crossell')) {
                pdpListName = listName.replace('placeholder', 'CS');
              }
              else if (upSellCrossSellSelector.hasClass('horizontal-upell')) {
                pdpListName = listName.replace('placeholder', 'US');
              }
              else if (upSellCrossSellSelector.hasClass('horizontal-related')) {
                pdpListName = listName.replace('placeholder', 'RELATED');
              }
            }

            impression.list = pdpListName;
            impression.position = count_pdp_items;
            impressions.push(impression);
            drupalSettings.impressions_position[$(this).attr('data-nid') + '-' + pdpListName] = count_pdp_items;
            count_pdp_items++;
          }
        });

        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, impressions);
      }
      else if ($.inArray(gtmPageType, impressionPages) !== -1) {
        var count = productLinkProcessedSelector.length + 1;

        if (productLinkSelector.length > 0) {
          productLinkSelector.each(function () {
            if (!$(this).hasClass('impression-processed')) {
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
       * Fire checkoutOption on cart page.
       */
      if (gtmPageType === 'cart page' && drupalSettings.user.uid !== 0) {
        Drupal.alshaya_seo_gtm_push_checkout_option('Logged In', 1);
      }

      /**
       * Tracking New customers.
       */
      cartCheckoutLoginSelector.find('a[gtm-type="checkout-as-guest"]').once('js-event').on('click', function () {
        Drupal.alshaya_seo_gtm_push_checkout_option('Guest Login', 1);
      });

      /**
       * Tracking Returning customers.
       */
        cartCheckoutLoginSelector.find('button[gtm-type="checkout-signin"]').once('js-event').on('mousedown', function () {
        Drupal.alshaya_seo_gtm_push_checkout_option('New Login', 1);
      });

      /**
       * Tracking Home Delivery.
       */
      if ((cartCheckoutDeliverySelector.length !== 0) &&
        (subDeliveryOptionSelector.find('.form-type-radio').length === 0)) {
        // Fire checkout option event if home delivery option is selected by default on delivery page.
        if (cartCheckoutDeliverySelector.find('div[gtm-type="checkout-home-delivery"]').once('js-event').hasClass('active--tab--head')) {
          deliveryType = 'Home Delivery';
        }
      }

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
          dataLayer.push({
            event: 'VirtualPageview',
            virtualPageURL: ' /virtualpv/click-and-collect/step2/select-store',
            virtualPageTitle: 'C&C Step 2 – Select Store'
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
          if (listName.includes('placeholder')) {
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
      if ($('.paragraph--type--promo-block').length > 0 && $('.field--name-body').length > 0 && (context === document)) {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($('.paragraph--type--promo-block, .field--name-body, .field--name-body > div[class^="rectangle"]:visible'), gtmPageType, 'promotionImpression');
      }

      else if ($('.paragraph--type--promo-block').length > 0 && (context === document)) {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($('.paragraph--type--promo-block'), gtmPageType, 'promotionImpression');
        }

        // Tracking promotion image view inside body field.
      else if ($('.field--name-body').length > 0 && (context === document)) {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($('.field--name-body'), gtmPageType, 'promotionImpression');
        }

      // Tracking of homepage banner.
      $('.c-content__slider .field--name-field-banner').each(function () {
        $(this).once('js-event').on('click', function () {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), gtmPageType, 'promotionClick');
        });
      });

      // Tracking view of homepage banner in body.
      $('.field--name-body').each(function () {
        $(this).once('js-event').on('click', function () {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), gtmPageType, 'promotionClick');
        });
      });

      // Tracking view of promotions.
      $('.paragraph--type--promo-block').each(function () {
        $(this).once('js-event').on('click', function () {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), gtmPageType, 'promotionClick');
        });
      });

      // Tracking images in rectangle on homepage.
      $('.field--name-body > div[class^="rectangle"]:visible').each(function () {
        $(this).once('js-event').on('click', function () {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), gtmPageType, 'promotionClick');
        });
      });

      // Tracking promotion banner on PLP.
      if (listName === 'PLP') {
        if ($('.views-field-field-promotion-banner').length > 0 && (context === document)) {
          if ($(this).find('a').length > 0) {
            Drupal.alshaya_seo_gtm_push_promotion_impressions($('.views-field-field-promotion-banner'), 'PLP', 'promotionImpression');
          }
        }

        // Tracking click on promo banner PLP.
        $('.views-field-field-promotion-banner').each(function () {
          if ($(this).find('a').length > 0) {
            $(this).once('js-event').on('click', function () {
              Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), 'PLP', 'promotionClick');
            });
          }
        });
      }

      /**
       * Tracking clicks on fitler & sort options.
       */
      if (listName === 'PLP' || listName === 'Search Results Page') {
        var section = listName;
        if (listName === 'PLP') {
          section = $('h1.c-page-title').text().toLowerCase();
        }

        // Track facet filters.
        $('li.facet-item').once('js-event').on('click', function () {
          if ($(this).find('input.facets-checkbox').attr('checked') === undefined) {
            var selectedVal = $(this).find('label>span.facet-item__value').text();
            var facetTitle = $(this).parent('ul').siblings('h3.c-facet__title').text();
            var filterValue = facetTitle + ':' + selectedVal;
            filterValue = filterValue.trim();

            var data = {
              event: 'filter',
              siteSection: section.trim(),
              filterValue: filterValue
            };

            dataLayer.push(data);
          }
        });

        // Track sorts.
        $('select[name="sort_bef_combine"]', context).once('js-event').on('change', function () {
          var sortValue = $(this).find('option:selected').text();
          sortValue.trim();
          var data = {
            event: 'sort',
            siteSection: section,
            sortValue: sortValue
          };

          dataLayer.push(data);
        });
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
    if (impressions.length > 0) {
      var data = {
        event: 'productImpression',
        ecommerce: {
          currencyCode: currencyCode,
          impressions: impressions
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
      else if (gtmPageType === 'home page' || gtmPageType === 'department page') {
        var imgSrc = $(highlight).find('picture img').attr('src');
        if (typeof imgSrc === 'undefined') {
          imgSrc = $(highlight).find('img').attr('src');
        }
        creative = Drupal.url(imgSrc);
        position = key;
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
          promotions.push(promotion);
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
   * Helper function to fetch value for a query string.
   *
   * @param variable
   *
   * @returns {string}
   */
  Drupal.getQueryVariable = function (variable) {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
      var pair = vars[i].split('=');
      if (decodeURIComponent(pair[0]) === variable) {
        return decodeURIComponent(pair[1]);
      }
    }
  };

  /**
   * Helper function to fetch current user details.
   *
   * @returns {array}
   */
  Drupal.setUserDetailsInStorage = function () {
    var userDetails = {};
    userDetails.userID = drupalSettings.user.uid;
    userDetails.userEmailID = '';
    userDetails.userName = '';
    userDetails.userType = 'Guest User';
    userDetails.privilegeCustomer = 'Regular Customer';

    userDetails = JSON.stringify(userDetails);

    if (drupalSettings.user.uid !== 0) {
      $.ajax({
        url: drupalSettings.path.baseUrl + "get-user-details",
        type: "POST",
        async: false,
        success: function (response, status) {
          console.log(response);
          userDetails = response.user_data;
        },
      });
    }

    // Save in localStorage.
    localStorage.setItem('userDetails', userDetails);
  };

  /**
   * Helper function to push default datalayer variables.
   *
   * @param platformType
   * @param userDetails
   */
  Drupal.alshaya_seo_default_datalayer_push = function (platformType, userDetails) {
      dataLayer.push({
        platformType: platformType,
        userID: userDetails.userID,
        userEmailID: userDetails.userEmailID,
        userName: userDetails.userName,
        userType: userDetails.userType,
        privilegeCustomer: userDetails.privilegeCustomer
      });
  };

})(jQuery, Drupal, dataLayer);
