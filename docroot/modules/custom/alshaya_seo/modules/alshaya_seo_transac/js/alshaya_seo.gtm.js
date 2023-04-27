/**
 * @file
 * JS code to integrate with GTM.
 */
(function ($, Drupal, dataLayer) {

  window.GTM_CONSTANTS = {
    CART_ERRORS: 'cart errors',
    CHECKOUT_ERRORS: 'checkout errors',
    PAYMENT_ERRORS: 'other payment errors',
    GENUINE_PAYMENT_ERRORS: 'payment errors',
  };

  window.productRecommendationsSuffix = 'pr-';

  var mouseenterTime = 0;
  var gtm_execute_onetime_events = true;
  var productImpressions = [];
  var productImpressionsTimer = null;
  window.productListStorageKey = 'gtm_product_list';

  /**
   * Drupal Behaviour to set ReferrerInfo Data to storage.
   */
  Drupal.behaviors.setReferrerInfoData = {
    attach: function () {
      const currentPath = drupalSettings.path.currentPath;
      const referrer = document.referrer;
      const url = window.location.href;

      if(currentPath.includes('checkout/confirmation')) {
        // Remove referrerData and isSearchActivated from storage.
        Drupal.removeItemFromLocalStorage('referrerData');
        Drupal.removeItemFromLocalStorage('isSearchActivated');
      } else if (currentPath.includes('search')) {
        const referrerData = {
          pageType: 'Search Results Page',
          path: url,
          list: 'Search Results Page',
          previousPageType: '',
        };

        Drupal.addItemInLocalStorage('referrerData', referrerData);
        Drupal.removeItemFromLocalStorage('isSearchActivated');
      } else if (currentPath.includes('taxonomy/term')) {
        const listName =  $('body').attr('gtm-list-name');
        // Set PLP as referrerPageType
        const referrerData = {
          pageType: 'PLP',
          path: url,
          list: listName !== undefined ? listName : '',
          previousList: '',
        };

        Drupal.addItemInLocalStorage('referrerData', referrerData);
        Drupal.removeItemFromLocalStorage('isSearchActivated');
      } else if (currentPath.includes('node/')) {
        const listName = $('body').attr('gtm-list-name');
        const gtmContainer =  $('body').attr('gtm-container');
        // Check if node is of PDP type.
        if (gtmContainer === 'product detail page') {
          const referrerData = Drupal.getItemFromLocalStorage('referrerData');
          const isSearchActivated = Drupal.getItemFromLocalStorage('isSearchActivated');
          if (referrer === '' || (Drupal.hasValue(referrerData) && Drupal.hasValue(referrerData.path) && !referrerData.path.includes(referrer))) {
            if(isSearchActivated !== null && !isSearchActivated) {
              // Set PDP as referrerPageType only if referrer is not set,
              // Search is not active or
              // Referrer does not match with referrerPath.
              const referrerData = {
                pageType: 'PDP',
                path: url,
                list: listName !== undefined ? listName : '',
                previousList: '',
              };

              Drupal.addItemInLocalStorage('referrerData', referrerData);
              Drupal.removeItemFromLocalStorage('isSearchActivated');
            }
          }
        }
        else {
          const referrerData = {
            pageType: gtmContainer !== undefined ? gtmContainer : '',
            path: url,
            list: listName !== undefined ? listName : '',
            previousList: '',
          };

          Drupal.addItemInLocalStorage('referrerData', referrerData);
          Drupal.removeItemFromLocalStorage('isSearchActivated');
        }
      }
    }
  };

  Drupal.behaviors.seoGoogleTagManager = {
    attach: function (context, settings) {
      $('body').once('alshaya-seo-gtm').on('variant-selected magazinev2-variant-selected', '.sku-base-form', function (event, variant, code) {
        var product = $(this).closest('[gtm-type="gtm-product-link"]');
        var sku = $(this).attr('data-sku');
        var productKey = (product.attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';
        var productInfo = window.commerceBackend.getProductData(sku, productKey);

        if (typeof productInfo === 'undefined' || !productInfo) {
          return;
        }

        // We get variant details in event object for magazine v2 layout.
        if ((typeof event.detail !== 'undefined') && (typeof event.detail.variant !== 'undefined')) {
          variant = event.detail.variant;
        }
        var variantInfo = productInfo['variants'][variant];
        // Return if variant data not available.
        if (typeof variantInfo === 'undefined') {
          return;
        }

        product.attr('gtm-product-sku', variant);
        product.attr('gtm-price', variantInfo['gtm_price']);
        product.attr('gtm-main-sku', variantInfo['parent_sku']);
      });

      // For simple grouped products.
      $('article.entity--type-node').once('alshaya-seo-gtm-simple-grouped').on('group-item-selected', function (event, variant) {
        var sku = $(this).attr('data-sku');
        var productKey = ($(this).attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';
        if (typeof drupalSettings[productKey][sku] === 'undefined'
          || typeof drupalSettings[productKey][sku]['group'][variant] === 'undefined') {
          return;
        }

        var variantInfo = drupalSettings[productKey][sku]['group'][variant];

        $(this).attr('gtm-main-sku', variant);
        $(this).attr('gtm-product-sku', variant);
        $(this).attr('gtm-price', variantInfo['gtm_price']);
      });

      $('.sku-base-form').once('js-event').on('product-add-to-cart-success', function (event) {
        // Return if noGtm flag is set to true. For example, in sofa
        // and sectional feature GTM is handled in react so we
        // don't need GTM push to be handled here in the listner.
        if (typeof event.detail.noGtm !== 'undefined' && event.detail.noGtm) {
          return;
        }

        var addedProduct = $(this).closest('[gtm-type="gtm-product-link"]');
        if (addedProduct.length === 0) {
          return;
        }
        var size = $('.form-item-configurables-size select option:selected', $(this)).text();
        var selectedVariant = '';
        var quantity = null;

        // Since markup is different for magazine v2 layout, we fetch values
        // differently.
        if ($('body').hasClass('magazine-layout-v2')) {
          quantity = parseInt($('.magv2-qty-container .magv2-qty-input', $(this)).val());
          if (addedProduct.attr('gtm-sku-type') === 'configurable') {
            selectedVariant = $(this).attr('variantselected');
          }
        }
        else {
          quantity = parseInt($('.form-item-quantity select', $(this)).val());
          if (addedProduct.attr('gtm-sku-type') === 'configurable') {
            selectedVariant = $('.selected-variant-sku', $(this)).val();
          }
        }

        var product = Drupal.alshaya_seo_gtm_get_product_values(addedProduct);

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

        var cart_total_count = (event.detail.cartData !== null) ? event.detail.cartData.items_qty : null;

        // Add product view type as 'recommendations_popup' if
        // view mode is modal.
        if (Drupal.hasValue(product.data_vmode)
          && product.data_vmode === 'modal') {
          product.product_view_type = 'recommendations_popup';
        }

        // Push product addToCart event to GTM.
        Drupal.alshayaSeoGtmPushAddToCart(product, cart_total_count);
      });

      // Push GTM event on add to cart failure.
      $('.sku-base-form').once('js-event-fail').on('product-add-to-cart-failed', function (e) {
        const sku = (e.detail.productData.parentSku !== 'undefined') ? e.detail.productData.parentSku : null;
        // Set Event label.
        var label = 'Update cart failed for Product [' + sku + '] ';
        label = label + e.detail.productData.options.join(', ');
        Drupal.alshayaSeoGtmPushAddToCartFailure(label, e.detail.message);
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
      if (typeof drupalSettings.userDetails.userID === 'undefined') {
        userDetails = drupalSettings.user;
        userId = userDetails.uid;
        userDetails.userID = userId;
      }
      // userDetails is set in case of google/facebook login.
      else  {
        userDetails = drupalSettings.userDetails;
        userId = userDetails.userID;
      }

      if (!Drupal.getItemFromLocalStorage('userID')) {
        Drupal.addItemInLocalStorage('userID', userId);
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
            cartCheckoutPaymentSelector.length !== 0) {
          delete userDetails.privilegeCustomer;
        }

        // Push on all pages except confirmation page.
        if (orderConfirmationPage.length === 0) {
          dataLayer.push(userDetails);
        }
      });
      // Push for 404 Pages.
      if(gtmPageType === 'page not found'){
        dataLayer.push({
          event: '404_error'
        });
      }

      // If we receive an empty page type, set page type as not defined.
      if (gtmPageType === 'not defined') {
        if (document.location.pathname.indexOf('/' + drupalSettings.path.currentLanguage + '/user') == 0) {
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

      if ((isRegistrationSuccessPage) && (context === document)) {
        Drupal.alshaya_seo_gtm_push_signin_type('Registration Success');
      }

      // Cookie based events, only to be processed once on page load.
      $(document).once('gtm-onetime').each(function () {
        // Check if social login window opened to avoid GTM push from
        // social login window.
        var socialWindow = false;
        if (window.name == 'ConnectWithSocialAuth') {
          var socialWindow = true;
        }
        // Check if user is login or not.
        var isUserLogin = Drupal.getItemFromLocalStorage('isUserLogin');
        // Check for user login type in cookies.
        var loginType = $.cookie('Drupal.visitor.alshaya_gtm_user_login_type');
        if (drupalSettings.user.uid && loginType === undefined && isUserLogin == null) {
          Drupal.alshaya_seo_gtm_push_signin_type('Login Success' , 'Email');
          Drupal.addItemInLocalStorage('isUserLogin', true);
        }

        // Fire sign-in success event on successful sign-in from parent window.
        if (!(socialWindow)
          && userDetails.userID !== undefined
          && userDetails.userID !== 0
          && loginType !== undefined
          && isUserLogin == null) {
          Drupal.alshaya_seo_gtm_push_signin_type('Login Success', loginType);
          Drupal.addItemInLocalStorage('userID', userDetails.userID);
          Drupal.addItemInLocalStorage('isUserLogin', true);
        }

        // Fire logout success event on successful sign-in.
        if (Drupal.getItemFromLocalStorage('userID')
          && Drupal.getItemFromLocalStorage('userID') != userDetails.userID
          && userDetails.userID === 0) {
          Drupal.alshaya_seo_gtm_push_signin_type('Logout Success');
          Drupal.addItemInLocalStorage('userID', userDetails.userID);
          $.removeCookie('Drupal.visitor.alshaya_gtm_user_login_type', {path: '/'});
          Drupal.removeItemFromLocalStorage('isUserLogin');
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
      cartCheckoutDeliverySelector.find('div[gtm-type="checkout-click-collect"] > a').once('delivery-option-event').on('click', function () {
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

          // Set updated product quantity.
          product.quantity = Math.abs(diffQty);

          // Set item's size as dimension6.
          if (!$.inArray('dimension6', settings.gtm.disabled_vars) && cartItem.attr('gtm-size')) {
            product.dimension6 = cartItem.attr('gtm-size');
          }

          if (diffQty < 0) {
            // Trigger removeFromCart.
            Drupal.alshayaSeoGtmPushRemoveFromCart(product);
          }
          else {
            // Trigger addToCart.
            Drupal.alshayaSeoGtmPushAddToCart(product);
          }
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

          // Trigger removeFromCart.
          Drupal.alshayaSeoGtmPushRemoveFromCart(product);
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
          .each(function () {
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
        .on('click', function () {
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
       * Helper function to mark position of elements in homepage/PDP slider.
       */
      $('.view-product-slider', context).once('mark-slider-items-position').each(function () {
        var count = 1;
        // In PDP it is seen that slick is already initialized by the time this
        // is executed while in homepage it is not.
        // So to prevent including slick-clones as actual items in our
        // calculations we use the :not pseudo-class.
        $(this).find('.views-row:not(.slick-cloned)').once('mark-item-position').each(function () {
          $(this).data('list-item-position', count++);
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
        if ((listName.indexOf('PLP') > -1) || listName === 'Search Results Page' || listName === 'Promotion') {
          var section = listName;
          if (listName.indexOf('PLP') > -1) {
            // As we have used the same markup to display search results page
            // title, use the h1 tag inside '#block-page-title' context.
            section = $('h1.c-page-title', $('#block-page-title')).text().toLowerCase();
          }
          // Track facet filters.
          $('li.facet-item', $('.block-facets-ajax')).once('js-event').on('click', function () {
            var selectedVal = typeof $(this).find('a').attr('data-drupal-facet-item-label') !== 'undefined'
              ? $(this).find('a').attr('data-drupal-facet-item-label').trim() : '';
            var facetTitle = $(this).find('a').attr('data-drupal-facet-label');
            // For promotion page.
            if (listName === 'Promotion') {
              facetTitle = $(this).parent('ul').attr('data-drupal-facet-alias').replaceAll("_", " ");
              selectedVal = $(this).find('a').attr('data-drupal-facet-item-value');
            }
            var data = {
              event: 'filter',
              siteSection: section.trim(),
              filterType: facetTitle,
              filterValue: selectedVal,
            };

            dataLayer.push(data);
          });

          // Track sorts.
          $('input[name="sort_bef_combine"]', context).once('js-event').on('change', function () {
            var sortValue = $("label[for='" + $(this).attr('id') + "']").first().text();
            sortValue.trim();
            var facetTitle = $('.fieldset-legend').first().html();
            var data = {
              event: 'sort',
              siteSection: section.trim(),
              filterType: facetTitle,
              filterValue: sortValue
            };

            dataLayer.push(data);
          });
        }
      }

      gtm_execute_onetime_events = false;
    }
  };

  // Simple proxy to dispatch a custom event on window.history.replaceState
  window.history.replaceState = new Proxy(window.history.replaceState, {
    apply: (target, thisArg, argArray) => {
      window.dispatchEvent(new CustomEvent('onAlshayaSeoReplaceState', {detail: { data: () => argArray }}));
      return target.apply(thisArg, argArray);
    },
  });

  // Processes the data and pushes it to gtm layer.
  window.addEventListener('onAlshayaSeoReplaceState', function (e) {
    let product = $('.entity--type-node[data-sku][data-vmode="full"]');
    // Convert the product to a jQuery object, if not already.
    if (!(product instanceof jQuery) && typeof product !== 'undefined') {
      product = $(product);
    }
    const lastUrl = location.href;
    const url = e.detail.data()[2];

    if (product !== null && product.length > 0 && !lastUrl.includes(url)) {
      var productObj = Drupal.alshaya_seo_gtm_get_product_values(product);
      // Dispatch a custom event to alter the product detail view object.
      document.dispatchEvent(new CustomEvent('onProductDetailView', { detail: { data: () => productObj } }));
      const cart = (typeof Drupal.alshayaSpc !== 'undefined') ? Drupal.alshayaSpc.getCartData() : null;
      // Prepare data.
      var data = {
        event: 'productDetailView',
        cart_items_count: (cart !== null) ? cart.items_qty : 0,
        ecommerce: {
          currencyCode: drupalSettings.gtm.currency,
          detail: {
            products: [productObj]
          }
        }
      };

      // Push into datalayer.
      dataLayer.push(data);
    }
  });

  // Push to GTM when add to bag product drawer is opened.
  document.addEventListener('drawerOpenEvent', function onDrawerOpen(e) {
    var $element = e.detail.triggerButtonElement.closest('article.node--view-mode-search-result');
    // Select the proper selector in case of matchback products.
    if (e.detail.elementViewMode == 'matchback' || e.detail.elementViewMode == 'matchback_mobile') {
      var $element = e.detail.triggerButtonElement.closest('article.entity--type-node');
    }
    if ($element) {
      Drupal.alshayaSeoGtmPushProductDetailView($element);
    }
  });

  /**
   * Function to provide product data object.
   *
   * @param product
   *   jQuery object which contains all gtm attributes.
   */
  Drupal.alshaya_seo_gtm_get_product_values = function (product) {
    // Convert the product to a jQuery object, if not already.
    if (!(product instanceof jQuery) && typeof product !== 'undefined') {
      product = $(product);
    }

    var mediaCount = 'image not available';

    if (product.attr('gtm-dimension4') && product.attr('gtm-dimension4') !== 'image not available') {
      mediaCount = parseInt(product.attr('gtm-dimension4'));
    }

    // Prepare default product data object.
    var productData = {
      name: '',
      id: product.attr('gtm-main-sku'),
      price: 0,
      category: '',
      variant: '',
      dimension2: '',
      dimension3: '',
      dimension4: mediaCount,
    };

    try {
      // Remove comma from price before passing through parseFloat.
      var amount = product.attr('gtm-price').replace(/\,/g,'');
      productData = {
        name: product.attr('gtm-name'),
        id: product.attr('gtm-main-sku'),
        price: parseFloat(amount),
        category: product.attr('gtm-category'),
        variant: product.attr('gtm-product-sku'),
        product_style_code: product.attr('gtm-product-style-code'),
        dimension2: product.attr('gtm-sku-type'),
        dimension3: product.attr('gtm-dimension3'),
        dimension4: mediaCount,
        data_vmode: product.attr('data-vmode')
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

      productData.list = '';

      if ($('body').is('[gtm-list-name]')) {
        // For all other pages, use gtm-list-name html attribute.
        // Except in PDP, to define full path from PLP.
        productData.list = $('body').attr('gtm-list-name').replace('PDP-placeholder', 'PLP');
      }
      // If list variable is set in local storage, retrieve it.
      var listValues = Drupal.getItemFromLocalStorage(productListStorageKey) || {};
      if (listValues
        && typeof listValues === 'object'
        && Object.keys(listValues).length
        && typeof listValues[productData.id] !== 'undefined') {
        // For SRP, use list value 'Search Result Page'.
        // For product recommendations use list value from local storage.
        if (listValues[productData.id] === 'Search Results Page'
          || !$('body').is('[gtm-list-name]')
          || listValues[productData.id].indexOf('match back') > -1
          || listValues[productData.id].indexOf(productRecommendationsSuffix) > -1) {
          productData.list = listValues[productData.id];
        }
      }

      // Check if local storage contains list data for DY product recommendations.
      if (productData.data_vmode == 'modal') {
        var dyListValues = Drupal.getItemFromLocalStorage('gtm_dy_product_list');
        if (dyListValues
          && typeof dyListValues.list !== 'undefined'
          && dyListValues.list !== '') {
          // Store updated listValues in local storage and product data.
          productData.list = listValues[productData.id] = dyListValues.list;
          if (drupalSettings.gtm && drupalSettings.gtm.productListExpirationMinutes) {
            Drupal.addItemInLocalStorage(productListStorageKey, listValues, drupalSettings.gtm.productListExpirationMinutes);
          }
          // Delete the local storage for gtm dy list so that this value is not
          // used again for other popups.
          Drupal.removeItemFromLocalStorage('gtm_dy_product_list');
        }
      }

      // Dispatch custom event to get list name. For the default value we use
      // the list name from the gtm attribute for the page. But for sections
      // like matchback, we need "match back" prefix to be added instead of
      // PDP/PLP, so this event will help us there.
      if (productData.data_vmode === 'matchback') {
      var gtmListNameEvent = new CustomEvent('getGtmListNameForProduct', {
        detail: {
          listName: productData.list,
          storedListValues: listValues,
          sku: productData.id,
        }
      });
      document.dispatchEvent(gtmListNameEvent);
      productData.list = gtmListNameEvent.detail.listName;
      // match back products should have all events with match back.
      if (productData.list.indexOf('match back') === -1) {
        productData.list = $('body').attr('gtm-list-name').replace('PDP-placeholder', 'match back');
      }
    }
      // Fetch referrerPageType from localstorage stop in case product is matchback product
      // and modal view mode.
      const referrerData = Drupal.getItemFromLocalStorage('referrerData');
      if(referrerData !== null
        && productData.data_vmode !== 'matchback'
        && productData.data_vmode !== 'modal') {
        if (referrerData.pageType === 'Search Results Page') {
          // For SRP, use list value 'Search Result Page'
          productData.list = referrerData.pageType;
        }
        else {
          let listName = '';
          if(referrerData.list !== undefined) {
            // Use Available referrerList data as list data,
            // If available.
            listName = referrerData.list;
          }
          else {
            // Fetch from gtm-list-name attribute.
            listName = $('body').attr('gtm-list-name');
          }

          // IF listName contains placeholder remove it.
          if (productData.list.indexOf('match back') == -1) {
            productData.list = listName.replace('PDP-placeholder', referrerData.pageType);
          }
        }
      }
    }
    catch (error) {
      // In case of error.
      Drupal.logJavascriptError('Uncaught errors', error);
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
      var statsText = $('.total-result-count .ais-Stats-text').text();
      var data = {
        event: 'productImpression',
        eventLabel2: Drupal.hasValue(statsText) ? statsText.match(/\d+/) + ' items' : '',
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
   * @param promoBlockDetails
   */
  Drupal.alshaya_seo_gtm_push_promotion_impressions = function (highlights, gtmPageType, event, promoBlockDetails = null) {
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
      else if (gtmPageType === 'footer promo panel') {
        creative = Drupal.url($(highlight).find('.field--name-field-banner img').attr('src'));
        // Slider behaviour when first item is repeated after last item
        // turning first item index to number of promotion items plus one.
        // Taking first position in such case.
        if ($(highlight).attr('data-slick-index') == promoBlockDetails.promotionItemCount) {
          position = 1;
        }
        else {
          position = Number($(highlight).attr('data-slick-index')) + 1;
        }
        var promoItemName = $(highlight).find('.field--name-field-title').attr('gtm-title').toUpperCase();
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
        if (fileName.lastIndexOf('.') !== -1 && gtmPageType !== 'footer promo panel') {
          fileName = fileName.substring(0, fileName.lastIndexOf('.'));
        }
        fileName = fileName.toLowerCase();

        if ((fileName !== undefined) && (fileName !== '') && (
          (fileName.indexOf('hp') === 0) ||
          (fileName.indexOf('mm') === 0) ||
          (fileName.indexOf('dp') === 0) ||
          (fileName.indexOf('lp') === 0) ||
          (fileName.indexOf('oth') === 0) ||
          (gtmPageType === 'footer promo panel')
        )) {
          var promotion = {
            creative: (gtmPageType === 'footer promo panel') ? decodeURI(fileName) : creative.replace(/\/en\/|\/ar\//, ''),
            id: (gtmPageType === 'footer promo panel') ? promoItemName : fileName,
            name: (gtmPageType === 'footer promo panel') ? promoBlockDetails.promoBlockLabel : gtmPageType,
            position: (gtmPageType === 'footer promo panel') ? position : 'slot' + position
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

    // Push product impressions to datalayer.
    Drupal.alshaya_seo_gtm_prepare_and_push_product_impression(null, null, null, {'type': 'product-click'});
    var product = Drupal.alshaya_seo_gtm_get_product_values(element);

    // On productClick, add product to product list in local storage.
    if (drupalSettings.gtm && drupalSettings.gtm.productListExpirationMinutes) {
      var listValues = Drupal.getItemFromLocalStorage(productListStorageKey) || {};
      // If listname used for product recommendation then extract only the first
      // part prior to '|'.
      if (listName && listName.indexOf(productRecommendationsSuffix) > -1) {
        listName = listName.split('|')[0];
      }
      listValues[product.id] = product.list = listName;
      Drupal.addItemInLocalStorage(productListStorageKey, listValues, drupalSettings.gtm.productListExpirationMinutes);
    }

    product.variant = '';
    if (position) {
      product.position = position;
    }

    const sku = product.id;
    let productSelector = document.querySelectorAll(`[data-sku="${sku}"]`);
    const isProductDataAvailable = (typeof productSelector[0] !== 'undefined');
    var data = {
      event: 'productClick',
      magento_product_id: isProductDataAvailable ? productSelector[0].getAttribute('gtm-magento-product-id') : null,
      stock_status: drupalSettings.dataLayerContent.stockStatus || (isProductDataAvailable ? productSelector[0].getAttribute('gtm-stock') : null),
      product_style_code: isProductDataAvailable ? productSelector[0].getAttribute('gtm-product-style-code') : null,
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
   * Helper function to push swatch click events to GTM.
   *
   * @param element
   */
   Drupal.alshayaSeoGtmPushSwatchClick = function (productData) {
    if (Drupal.hasValue(productData)) {
      var data = {
        event: 'colorInteraction',
        eventCategory: 'colorSwatch',
        eventAction: 'clicked-' + productData.color,
        eventLabel: productData.gtm_name + '_' + productData.sku,
        eventValue: 0,
        nonInteraction: 0,
      };
      dataLayer.push(data);
    }
  };

  /**
   * Helper function to push PLP and PDP ecommerce events to GTM.
   *
   * @param eventData
   *  Contains event details eg: 'eventLabel', 'eventAction' etc.
   */
  Drupal.alshayaSeoGtmPushEcommerceEvents = function (eventData) {
    if (Drupal.hasValue(eventData)) {
      var data = {
        event: 'ecommerce',
        eventCategory: 'ecommerce',
        eventAction: eventData.eventAction,
        eventLabel: eventData.eventLabel,
        eventLabel2: Drupal.hasValue(eventData.eventLabel2) ? eventData.eventLabel2 : '',
      };

      // Add @var product_view_type in quick view.
      if (Drupal.hasValue(eventData.product_view_type)) {
        data.product_view_type = eventData.product_view_type;
      }
      dataLayer.push(data);
    }
  };

  /**
   * Helper function to push swatch slider next/prev arrow click events to GTM.
   *
   * @param eventLabel
   *  Contains event label for the event.
   */
    Drupal.alshayaSeoGtmPushSwatchSliderClick = function (eventLabel) {
      var data = {
        event: 'swatches_chevronclick',
        eventCategory: 'swatches_chevronclick',
        eventAction: 'swatches_chevronclick',
        eventLabel: eventLabel,
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
   * @param loginType
   */
  Drupal.alshaya_seo_gtm_push_signin_type = function (eventAction, loginType = null) {
    var data = {
      event: 'eventTracker',
      eventCategory: 'Login & Register',
      eventAction: eventAction,
      eventLabel: loginType,
      eventValue: 0,
      nonInteraction: 0
    };
    // Add additional GTM variables for User registration.
    if (eventAction == 'Registration Success') {
      var data_additional = {};
      data_additional.registration_method = 'Email';
      data_additional.gender_selection = drupalSettings.alshaya_gtm_create_user_gender !== undefined ?
        drupalSettings.alshaya_gtm_create_user_gender : '';
      data_additional.email_preference = drupalSettings.alshaya_gtm_create_user_newsletter !== undefined ?
        drupalSettings.alshaya_gtm_create_user_newsletter : '';
      Object.assign(data, data_additional);
    }

    dataLayer.push(data);
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
   * @param prepareImpressionFunction
   *   The function to call which will prepare the product impressions.
   *   Take example of Drupal.alshaya_seo_gtm_prepare_impressions().
   *   The function will accept 3 parameters:
   *     1. context: The context in which to search for impressions.
   *     2. eventType: The type of the event, eg: 'scroll'.
   * @param context
   *   The context for which impressions is to be generated.
   * @param settings
   *    Any settings.
   * @param event
   *    The event object or a custom object containing at least the event type.
   *    Eg. {type: 'timer'} can also be sent as a value.
   */
  Drupal.alshaya_seo_gtm_prepare_and_push_product_impression = function (prepareImpressionFunction, context, settings, event) {
    var body = $('body');
    var currencyCode = body.attr('gtm-currency');
    var eventType = event.type;

    if (eventType === 'load') {
      productImpressions = prepareImpressionFunction(context, eventType);
      // We use splice so that by no chance we send more the required number of
      // items on page load.
      Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions.splice(0, drupalSettings.gtm.productImpressionDefaultItemsInQueue));
      productImpressionsTimer = window.setInterval(Drupal.alshaya_seo_gtm_prepare_and_push_product_impression, drupalSettings.gtm.productImpressionTimer, prepareImpressionFunction, context, settings, {type: 'timer'});
    }
    else if (eventType === 'search-results-updated') {
      // Push all previous impressions when new search is performed.
      Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions);
      // Clear the previous timer.
      window.clearInterval(productImpressionsTimer);
      // Send default number of items to datalayer for new seach.
      productImpressions = prepareImpressionFunction(context, eventType);
      // We use splice so that by no chance we send more the required number of
      // items on page load.
      Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions.splice(0, drupalSettings.gtm.productImpressionDefaultItemsInQueue));
      productImpressionsTimer = window.setInterval(Drupal.alshaya_seo_gtm_prepare_and_push_product_impression, drupalSettings.gtm.productImpressionTimer, prepareImpressionFunction, context, settings, { type: 'timer' });
    }
    else if (eventType === 'timer') {
      // This is to prevent the timer calling this function infinitely when
      // there are no impressions.
      if (productImpressions.length === 0) {
        window.clearInterval(productImpressionsTimer);
        productImpressionsTimer = null;
        return;
      }
      // Push required items currently in queue.
      Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions.splice(0, drupalSettings.gtm.productImpressionQueueSize));
      window.clearInterval(productImpressionsTimer);
      productImpressionsTimer = window.setInterval(Drupal.alshaya_seo_gtm_prepare_and_push_product_impression, drupalSettings.gtm.productImpressionTimer, prepareImpressionFunction, context, settings, { type: 'timer' });
    }
    else if (eventType === 'product-click' || eventType === 'pagehide') {
      // Push all impressions to data layer.
      Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions);
      window.clearInterval(productImpressionsTimer);
      // product-click can also happen for modals. Considering such a case we
      // restart the timer.
      if (eventType === 'product-click') {
        productImpressionsTimer = window.setInterval(Drupal.alshaya_seo_gtm_prepare_and_push_product_impression, drupalSettings.gtm.productImpressionTimer, prepareImpressionFunction, context, settings, { type: 'timer' });
      }
    }
    else if (eventType === 'plp-results-updated') {
      // Using concat instead of assignment since plp-results-updated event gets
      // triggered both on page load and clicking on load more button.
      productImpressions = productImpressions.concat(prepareImpressionFunction(context, eventType));
      if (productImpressionsTimer === null ) {
        // This is for page load where we push default number of items i.e. 4.
        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions.splice(0, drupalSettings.gtm.productImpressionDefaultItemsInQueue));
      }
      else {
        // This is when clicked on load more where we push existing items in
        // productImpressions and newly added items from load more.
        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions.splice(0, drupalSettings.gtm.productImpressionQueueSize));
      }
      // Setting timer event.
      productImpressionsTimer = window.setInterval(Drupal.alshaya_seo_gtm_prepare_and_push_product_impression, drupalSettings.gtm.productImpressionTimer, prepareImpressionFunction, context, settings, {type: 'timer'});
    }
    else if (eventType === 'wishlist-results-updated') {
      // Using concat instead of assignment since wishlist-results-updated event gets
      // triggered both on page load and clicking on load more button.
      productImpressions = productImpressions.concat(prepareImpressionFunction(context, eventType));
      if (productImpressionsTimer === null ) {
        // This is for page load where we push default number of items i.e. 4.
        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions.splice(0, drupalSettings.gtm.productImpressionDefaultItemsInQueue));
      }
      else {
        // This is when clicked on load more where we push existing items in
        // productImpressions and newly added items from load more.
        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions.splice(0, drupalSettings.gtm.productImpressionQueueSize));
      }
      // Setting timer event.
      productImpressionsTimer = window.setInterval(Drupal.alshaya_seo_gtm_prepare_and_push_product_impression, drupalSettings.gtm.productImpressionTimer, prepareImpressionFunction, context, settings, {type: 'timer'});
    }
    else {
      // This is for cases like scroll/carousel events.
      // Add new impressions to the global productImpressions.
      productImpressions = productImpressions.concat(prepareImpressionFunction(context, eventType));
      // If timer was unset previously when there were there were no impressions
      // then set it now.
      if (productImpressions.length > 0 && productImpressionsTimer === null) {
        productImpressionsTimer = window.setInterval(Drupal.alshaya_seo_gtm_prepare_and_push_product_impression, drupalSettings.gtm.productImpressionTimer, prepareImpressionFunction, context, settings, { type: 'timer' });
      }
      // Push if the global productImpressions length > max impressions size.
      if (productImpressions.length >= drupalSettings.gtm.productImpressionQueueSize) {
        Drupal.alshaya_seo_gtm_push_impressions(currencyCode, productImpressions.splice(0, drupalSettings.gtm.productImpressionQueueSize));
        window.clearInterval(productImpressionsTimer);
        productImpressionsTimer = window.setInterval(Drupal.alshaya_seo_gtm_prepare_and_push_product_impression, drupalSettings.gtm.productImpressionTimer, prepareImpressionFunction, context, settings, { type: 'timer' });
      }
    }
  }

  /**
   * Prepares product impressions.
   */
  Drupal.alshaya_seo_gtm_prepare_impressions = function (context, eventType) {
    var impressions = [];
    var body = $('body');
    var productLinkSelector = $('[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]:not(".impression-processed"):visible', context);
    var productLinkProcessedSelector = $('.impression-processed[gtm-type="gtm-product-link"][gtm-view-mode!="full"][gtm-view-mode!="modal"]', context);
    var listName = body.attr('gtm-list-name');
    // Send impression for each product added on page (page 1 or X).
    var count = productLinkProcessedSelector.length + 1;
    if (productLinkSelector.length > 0) {
      productLinkSelector.each(function () {
        var position = $(this).attr('data-insights-position');
        if (position === undefined) {
          $(this).attr('list-item-position', count);
          position = count;
        }
        if ($(this).isElementInViewPort(0, 10)) {
          $(this).addClass('impression-processed');
          var impression = Drupal.alshaya_seo_gtm_get_product_values($(this));
          impression.list = listName;
          impression.position = position;
          // Keep variant empty for impression pages. Populated only post add to cart action.
          impression.variant = '';
          impressions.push(impression);
          count++;
        }
        // On page load, process only the required number of
        // items and push to datalayer.
        if ((eventType === 'load' || eventType === 'plp-results-updated' || eventType === 'wishlist-results-updated') && (impressions.length == drupalSettings.gtm.productImpressionDefaultItemsInQueue)) {
          // This is to break out from the .each() function.
          return false;
        }
      });
    }
    return impressions;
  };

  /**
   * Function to check if current page is search page.
   *
   * @return {boolean}
   *
   */
  function isPageTypeSearch() {
    return $('#alshaya-algolia-search').is(":visible");
  }

  /**
   * Function to check if current page is listing page.
   *
   * @return {boolean}
   *
   */
  function isPageTypeListing() {
    var gtmList = $('body').attr('gtm-list-name');
    return gtmList !== undefined
      && (gtmList.indexOf('PLP') !== -1 || gtmList.indexOf('Promotion') !== -1);
  }

  /**
   * Function to push product detail view event to data layer.
   *
   * @param {object} productContext
   *   The jQuery HTML object containing GTM attributes for the product.
   */
  Drupal.alshayaSeoGtmPushProductDetailView = function (productContext) {
    var product = Drupal.alshaya_seo_gtm_get_product_values(productContext);
    // Dispatch a custom event to alter the product detail view object.
    document.dispatchEvent(new CustomEvent('onProductDetailView', { detail: { data: () => product } }));
    const cart = (typeof Drupal.alshayaSpc !== 'undefined') ? Drupal.alshayaSpc.getCartData() : null;

    var data = {
      event: 'productDetailView',
      cart_items_count: (cart !== null) ? cart.items_qty : 0,
      ecommerce: {
        currencyCode: drupalSettings.gtm.currency,
        detail: {
          products: [product]
        }
      }
    };

    // Push to productDetailView event if quick-view class exits.
    // Check if productContext is Array.
    let elementContext = productContext[0] ? productContext[0] : productContext;
    if (elementContext.classList !== undefined && elementContext.classList.contains('quick-view')) {
      data.product_view_type = 'quick_view';
    }

    // Add product view type as 'recommendations_popup' if view mode is modal.
    if (Drupal.hasValue(product.data_vmode) && product.data_vmode === 'modal') {
      data.product_view_type = 'recommendations_popup';
    }

    dataLayer.push(data);
  }

  /**
   * Function to push product addToCart event to data layer.
   *
   * @param {object} product
   *   The jQuery HTML object containing GTM attributes for the product.
   */
  Drupal.alshayaSeoGtmPushAddToCart = function (product, cart_total_count = null) {
    // Remove product position: Not needed while adding to cart.
    delete product.position;

    // Calculate metric 1 value.
    product.metric2 = product.price * product.quantity;

    // Remove product_view_type from product if view type is set.
    var enable_quickview = '';
    if (typeof product.product_view_type !== 'undefined') {
      enable_quickview = product.product_view_type;
      delete product.product_view_type;
    }
    var productData = {
      event: 'addToCart'
    };

    // Adding SRP specific GTM list attribute.
    if (isPageTypeSearch()) {
      productData.eventAction = 'Add to Cart on Search';
    }
    // Adding PLP specific GTM list attribute.
    else if (isPageTypeListing()) {
      productData.eventAction = 'Add to Cart on Listing';
    }
    if (cart_total_count == null) {
      const cart = (typeof Drupal.alshayaSpc !== 'undefined') ? Drupal.alshayaSpc.getCartData() : null;
      cart_total_count = (cart !== null) ? cart.items_qty : 0
    }
    productData.ecommerce = {
      currencyCode: drupalSettings.gtm.currency,
      cart_items_count: cart_total_count,
      add: {
        products: [
          product
        ]
      }
    };

    // Add product_view_type outside ecommerce.
    if (enable_quickview) {
      productData.product_view_type = enable_quickview;
    }
    dataLayer.push(productData);
  }

  /**
   * Function to push product removeFromCart event to data layer.
   *
   * @param {object} product
   *   The jQuery HTML object containing GTM attributes for the product.
   */
  Drupal.alshayaSeoGtmPushRemoveFromCart = function (product) {
    // Remove product position: Not needed while removing from cart.
    delete product.position;

    // Calculate metric 1 value.
    product.metric2 = -1 * product.quantity * product.price;

    var productData = {
      event: 'removeFromCart'
    };

    // Adding SRP specific GTM list attribute.
    if (isPageTypeSearch()) {
      productData.eventAction = 'Remove from Cart on Search';
    }
    // Adding PLP specific GTM list attribute.
    else if (isPageTypeListing()) {
      productData.eventAction = 'Remove from Cart on Listing';
    }

    productData.ecommerce = {
      currencyCode: drupalSettings.gtm.currency,
      remove: {
        products: [
          product
        ]
      }
    };

    // Delete product from product list in local storage.
    if (drupalSettings.gtm && drupalSettings.gtm.productListExpirationMinutes) {
      var listValues = Drupal.getItemFromLocalStorage(productListStorageKey) || {};

      if (listValues
        && typeof listValues === 'object'
        && Object.keys(listValues).length
        && typeof listValues[productData.id] !== 'undefined') {
        delete listValues[product.id];
        Drupal.addItemInLocalStorage(productListStorageKey, listValues, drupalSettings.gtm.productListExpirationMinutes);
      }
    }

    dataLayer.push(productData);
  }

  /**
   * Function to push GTM event to data layer on add to cart failure.
   */
  Drupal.alshayaSeoGtmPushAddToCartFailure = function (label, message) {
    Drupal.logJavascriptError(label, message, GTM_CONSTANTS.CART_ERRORS);
  }

  // Ajax command to push deliveryAddress Event.
  $.fn.triggerDeliveryAddress = function () {
    dataLayer.push({event: 'deliveryAddress', eventLabel: 'deliver to this address'});
  };

  /**
   * Log errors and track on GA.
   *
   * @param context
   * @param error
   * @param category
   */
  Drupal.logJavascriptError = function (context, error, category) {
    var message = (error && error.message !== undefined)
      ? error.message
      : error;

    // We want message to be sent as string always.
    if ($.type(message) !== 'string') {
      message = JSON.stringify(message);
    }
    var errorData = {
      event: 'eventTracker',
      eventCategory: category || 'unknown errors',
      eventLabel: context,
      eventAction: decodeURIComponent(message),
      eventPlace: 'Error occurred on ' + window.location.href,
      eventValue: 0,
      nonInteraction: 0,
    };

    if (category == GTM_CONSTANTS.PAYMENT_ERRORS || category == GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS) {
      var rawCartData = window.commerceBackend.getRawCartDataFromStorage();
      errorData.cartTotalValue = Drupal.hasValue(rawCartData.totals.subtotal_incl_tax) ?
        rawCartData.totals.subtotal_incl_tax : 0;
    }
    try {
      // Log error on console.
      if (drupalSettings.gtm.log_errors_to_console !== undefined
        && drupalSettings.gtm.log_errors_to_console) {
        console.log(errorData);
      }

      if (Drupal.logViaDataDog !== undefined) {
        Drupal.logViaDataDog('warning', 'Log from Drupal.logJavascriptError.', errorData);
      }

      // Track error on GA.
      if (drupalSettings.gtm.log_errors_to_ga !== undefined
        && drupalSettings.gtm.log_errors_to_ga
        && dataLayer !== undefined) {
        dataLayer.push(errorData);
      }
    } catch (e) {
      // Do nothing.
    }
  };

  // Push the errors to GA if enabled.
  // If TrackJS is enabled we let it track the errors.
  if (drupalSettings.gtm.log_errors_to_ga !== undefined
    && drupalSettings.gtm.log_errors_to_ga
    && typeof window.TrackJS === 'undefined'
    && typeof window.DD_LOGS === 'undefined') {
    window.onerror = function (message, url, lineNo, columnNo, error) {
      if (error !== null) {
        Drupal.logJavascriptError('Uncaught errors', error);
      } else if (message !== null) {
        Drupal.logJavascriptError('Uncaught errors', message);
      }
      return true;
    };
  }

})(jQuery, Drupal, dataLayer);
