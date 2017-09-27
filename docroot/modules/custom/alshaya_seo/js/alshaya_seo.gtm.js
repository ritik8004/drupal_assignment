/**
 * @file
 * JS code to integrate with GTM.
 */

(function ($, Drupal, dataLayer) {
  'use strict';

  var mouseenterTime = 0;
  var gtm_execute_onetime_events = true;

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
      var subDeliveryOptionSelector = $('#shipping_methods_wrapper .shipping-methods-container .js-webform-radios', context);
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

      // List of Pages where we need to push out list of product being rendered to GTM.
      var impressionPages = [
        'home page',
        'search result page',
        'product listing page',
        'product detail page',
        'department page'
      ];

      // If we receive an empty page type, set page type as not defined.
      if (gtmPageType === undefined) {
        gtmPageType = 'not defined';
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
          var keyword = $(this).val();
          var noOfResult = parseInt($('.view-header', context).text().replace(Drupal.t('items'), '').trim());

          if ((noOfResult === 0) || (isNaN(noOfResult))) {
            dataLayer.push({
              event: 'internalSearch',
              searchKeyword: keyword,
              noOfResult: 0
            });
          }
        });
      }

      if ((isRegistrationSuccessPage) && (context === document)) {
        Drupal.alshaya_seo_gtm_push_signin_type('registration success');
      }

      // Cookie based events, only to be processed once on page load.
      $(document).once('gtm-onetime').each(function () {
        // Fire sign-in success event on successful sign-in.
        if ($.cookie('Drupal.visitor.alshaya_gtm_user_logged_in') !== undefined) {
          dataLayer.push({
            event: 'User Login & Register',
            signinType: 'sign-in success'
          });

          $.removeCookie('Drupal.visitor.alshaya_gtm_user_logged_in', {path: '/'});
        }

        // Fire logout success event on successful sign-in.
        if ($.cookie('Drupal.visitor.alshaya_gtm_user_logged_out') !== undefined) {
          dataLayer.push({
            event: 'User Login & Register',
            signinType: 'logout success'
          });

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

      /** Track coupon code application. **/
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

      /** Track store finder clicks. **/
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

      if (isCCPage && gtm_execute_onetime_events) {
        if ($('li.select-store', context).length > 0) {
          var keyword = $('input#edit-store-location').val();
          var resultCount = $('li.select-store', context).length;
          Drupal.alshaya_seo_gtm_push_store_finder_search(keyword, 'checkout', resultCount);
        }
      }

      /** Impressions tracking on listing pages with Products. **/
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
            if (upSellCrossSellSelector.hasClass('horizontal-crossell')) {
              pdpListName = listName + '-CS';
            }
            else if (upSellCrossSellSelector.hasClass('horizontal-upell')) {
              pdpListName = listName + '-US';
            }
            else if (upSellCrossSellSelector.hasClass('horizontal-related')) {
              pdpListName = listName + '-RELATED';
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

      /** Add to cart GTM .**/
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

          if (responseMessage === 'success') {
            Drupal.alshaya_seo_gtm_push_lead_type('footer');
          }
        }
      });

      /** Quantity update in cart. **/
      // Trigger removeFromCart & addToCart events based on the quantity update on cart page.
      $('select[gtm-type="gtm-quantity"]').focus(function () {
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

          // Set item's size as dimension1.
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
            event: event,
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
        }
      });

      /** Remove Product from cart .**/
      // Add click handler to fire 'removeFromCart' event to GTM.
      removeCartSelector.once('js-event').each(function () {
        // Get selector holding details around the product.
        var removeItem = $(this).closest('td.quantity').siblings('td.name').find('[gtm-type="gtm-remove-cart-wrapper"]');

        removeItem.on('cart-item-removed', function () {
          var product = Drupal.alshaya_seo_gtm_get_product_values(removeItem);

          // Set product quantity to the number of items selected for quantity.
          product.quantity = $(this).closest('tr').find('td.quantity select').val();

          // Set selected size as dimension1.
          product.dimension1 = removeItem.attr('gtm-size');

          // Remove product position: Not needed while removing item from cart.
          delete product.position;

          product.metric1 = -1 * product.quantity * product.price;

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

      /** Tracking New customers .**/
      cartCheckoutLoginSelector.find('a[gtm-type="checkout-as-guest"]').once('js-event').on('click', function () {
        Drupal.alshaya_seo_gtm_push_customer_type('checkout as guest');
      });

      /** Tracking Returning customers .**/
      cartCheckoutLoginSelector.find('input[gtm-type="checkout-signin"]').once('js-event').on('click', function () {
        Drupal.alshaya_seo_gtm_push_customer_type('registered customer');
      });

      /** Tracking Home Delivery .**/
      if ((cartCheckoutDeliverySelector.length !== 0) &&
        (subDeliveryOptionSelector.find('.form-type-radio').length === 0)) {
        // Fire checkout option event if home delivery option is selected by default on delivery page.
        if (cartCheckoutDeliverySelector.find('div[gtm-type="checkout-home-delivery"]').once('js-event').hasClass('active--tab--head')) {
          Drupal.alshaya_seo_gtm_push_checkout_option('Home Delivery', 2);
        }
      }

      subDeliveryOptionSelector.find('.form-type-radio').once('js-event').each(function () {
        // Push default selected sub-delivery option to GTM.
        if ($(this).find('input[checked="checked"]').length > 0) {
          var selectedMethodLabel = $(this).find('.shipping-method-title').text();
          Drupal.alshaya_seo_gtm_push_checkout_option(selectedMethodLabel, 3);
        }

        // Attach change event listener to shipping method radio buttons.
        $(this).change(function () {
          var selectedMethodLabel = $(this).find('.shipping-method-title').text();
          Drupal.alshaya_seo_gtm_push_checkout_option(selectedMethodLabel, 3);
        });
      });

      /** GTM virtual page tracking for click & collect journey. **/
      if (isCCPage) {
        if ($('#store-finder-wrapper', context).length > 0) {
          dataLayer.push({
            event: 'VirtualPageview',
            virtualPageURL: '/virtualpv/click-and-collect/step1/click-and-collect-view',
            virtualPageTitle: 'C&C Step 1 – Click and Collect View'
          });

          Drupal.alshaya_seo_gtm_push_checkout_option('Click & Collect', 2);
        }

        $('.store-actions a.select-store', context).once('js-event').click(function () {
          dataLayer.push({
            event: 'VirtualPageview',
            virtualPageURL: ' /virtualpv/click-and-collect/step2/select-store',
            virtualPageTitle: 'C&C Step 2 – Select Store'
          });
        });
      }

      /** Tracking selected payment option .**/
      // Fire this only if on checkout Payment option page & Ajax response brings in cart-checkout-payment div.
      if ((cartCheckoutPaymentSelector.length !== 0) && ($('fieldset[gtm-type="cart-checkout-payment"]', context).length > 0)) {
        var preselectedMethod = $('[gtm-type="cart-checkout-payment"] input:checked');
        if (preselectedMethod.length === 1) {
          var preselectedMethodLabel = preselectedMethod.siblings('label').find('.method-title').text();
          if (drupalSettings.path.currentLanguage === 'ar') {
            preselectedMethodLabel = drupalSettings.alshaya_payment_options_translations[preselectedMethodLabel];
          }
          Drupal.alshaya_seo_gtm_push_checkout_option(preselectedMethodLabel, 4);
        }
      }

      /** Product Click Handler .**/
      // Add click link handler to fire 'productClick' event to GTM.
      productLinkSelector.each(function () {
        $(this).once('js-event').on('click', function (e) {
          var that = $(this);
          var position = $('.views-infinite-scroll-content-wrapper .c-products__item').index(that.closest('.c-products__item')) + 1;

          Drupal.alshaya_seo_gtm_push_product_clicks(that, currencyCode, listName, position);
        });
      });

      if ($(context).find('article[data-vmode="modal"]').length === 1) {
        var product = Drupal.alshaya_seo_gtm_get_product_values($(context).find('article[data-vmode="modal"]'));

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

      /** Product click handler for Modals. **/
      // Add click link handler to fire 'productClick' event to GTM.
      $('a[href*="product-quick-view"]').each(function () {
        $(this).once('js-event').on('click', function (e) {
          var that = $(this).closest('article[data-vmode="teaser"]');
          var position = '';

          if (that.closest('.horizontal-crossell').length > 0) {
            subListName = listName + '-CS';
          }
          else if (that.closest('.horizontal-upell').length > 0) {
            subListName = listName + '-US';
          }
          else if (that.closest('.horizontal-related').length > 0) {
            subListName = listName + '-RELATED';
          }

          // position = $('.view-product-slider .owl-item').index(that.closest('.owl-item')) + 1;
          position = drupalSettings.impressions_position[that.attr('data-nid') + '-' + subListName];
          Drupal.alshaya_seo_gtm_push_product_clicks(that, currencyCode, subListName, position);
        });
      });


      var highlightsPosition = 1;
      topNavLevelOneSelector.once('set-positions').find('.highlights [gtm-type="gtm-highlights"]').each(function () {
        $(this).data('position', highlightsPosition);
        highlightsPosition++;
      });

      /** Tracking internal promotion impressions. **/
      // Tracking menu level promotions.
      topNavLevelOneSelector.each(function () {
        $(this).once('js-event').mouseenter(function () {
          mouseenterTime = (new Date()).getTime();
        }).mouseleave(function () {
          var mouseOverTime = (new Date()).getTime() - mouseenterTime;
          if ((mouseOverTime >= 2000) && ($(this).hasClass('has-child'))) {
            var highlights = $(this).find('.highlights [gtm-type="gtm-highlights"]');

            if (highlights.length > 0) {
              Drupal.alshaya_seo_gtm_push_promotion_impressions(highlights, 'Top Navigation');
            }
          }
        });
      });

      $('[gtm-type="gtm-highlights"]').once('js-event').on('click', function () {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), 'Top Navigation', 'promotionClick');
      });

      if ($('.paragraph--type--promo-block').length > 0) {
        Drupal.alshaya_seo_gtm_push_promotion_impressions($('.paragraph--type--promo-block'), gtmPageType);
      }

      // Tracking view of promotions.
      $('.paragraph--type--promo-block').each(function () {
        $(this).once('js-event').on('click', function () {
          Drupal.alshaya_seo_gtm_push_promotion_impressions($(this), gtmPageType, 'promotionClick');
        });
      });

      /** Tracking clicks on fitler & sort options. **/
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

            var data = {
              event: 'filter',
              section: section,
              filterValue: filterValue
            };

            dataLayer.push(data);
          }
        });

        // Track sorts.
        $('select[name="sort_bef_combine"]', context).once('js-event').on('change', function () {
          var sortValue = $(this).find('option:selected').text();
          var data = {
            event: 'sort',
            section: section,
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
      brand: product.attr('gtm-brand'),
      category: product.attr('gtm-category'),
      variant: product.attr('gtm-product-sku'),
      position: 1,
      dimension1: '',
      dimension2: product.attr('gtm-dimension2'),
      dimension3: product.attr('gtm-dimension3'),
      dimension4: mediaCount,
      dimension5: product.attr('gtm-sku-type'),
      dimension8: product.attr('gtm-dimension8'),
      metric7: parseFloat(product.attr('gtm-metric7')),
      metric1: product.attr('gtm-cart-value')
    };

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

      if (gtmPageType === 'Top Navigation') {
        creative = Drupal.url($(highlight).find('.field--name-field-highlight-image img').attr('src'));
        position = $(highlight).data('position');
      }
      else {
        if ($(highlight).parent().find('a').length === 0) {
          return;
        }

        creative = Drupal.url($(highlight).find('.field--name-field-banner img').attr('src'));
        position = parseInt($('.paragraph--type--promo-block').index($(highlight))) + 1;
      }

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

      var promotion = {
        id: fileName,
        name: gtmPageType,
        position: 'slot' + position
      };

      promotions.push(promotion);
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
    var product = Drupal.alshaya_seo_gtm_get_product_values(element);
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
   * Helper funciton to push sign-in type event.
   *
   * @param signinType
   */
  Drupal.alshaya_seo_gtm_push_signin_type = function (signinType) {
    dataLayer.push({event: 'User Login & Register', signinType: signinType});
  };

  /**
   * Helper function to track store finder search.
   *
   * @param keyword
   * @param location
   * @param resultCount
   */
  Drupal.alshaya_seo_gtm_push_store_finder_search = function (keyword, location, resultCount) {
    dataLayer.push({
      event: 'findStore',
      fsLocation: location,
      fsKeyword: keyword,
      fsNoOfResult: resultCount
    });
  };

})(jQuery, Drupal, dataLayer);
