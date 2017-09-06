/**
 * @file
 * Store Finder - PDP.
 */

(function ($, Drupal) {
  'use strict';

  // Coordinates of the user's location.
  var asCoords = null;
  // Last checked SKU (or variant SKU).
  var lastSku = null;
  // Last coords.
  var lastCoords = null;
  // Geolocation permission, set default to false to show search form.
  // When user doesn't react to location permission.
  var geoPerm = false;
  // Check records already exists.
  var records = false;
  // Display search form.
  var displaySearchForm;

  Drupal.pdp = Drupal.pdp || {};
  Drupal.geolocation = Drupal.geolocation || {};
  Drupal.click_collect = Drupal.click_collect || {};

  Drupal.behaviors.pdpClickCollect = {
    attach: function (context, settings) {
      if (typeof Drupal.geolocation.loadGoogle === 'function') {
        $('.click-collect-form').once('autocomplete-init').each(function () {
          // First load the library from google.
          Drupal.geolocation.loadGoogle(function () {
            var field = $('.click-collect-form').find('input[name="location"]')[0];
            new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.setStoreCoords]);
          });
        });
      }

      $('#pdp-stores-container', context).once('initiate-stores').each(function () {
        // Get the permission track the user location.
        Drupal.click_collect.getCurrentPosition(Drupal.pdp.LocationSuccess, Drupal.pdp.LocationError);

        // Check if we have to show the block as disabled. Since accordion classes
        // are added in JS, this is handled in JS.
        if ($(this).attr('state') === 'disabled') {
          var accordionStatus = $('#pdp-stores-container.click-collect').accordion('option', 'active');
          if (typeof accordionStatus === 'number' && accordionStatus === 0) {
            $('#pdp-stores-container.click-collect').accordion('option', 'active', false);
          }
          $('#pdp-stores-container.c-accordion-delivery-options').accordion('option', 'disable', true);
        }
        else {
          // Get the permission track the user location.
          Drupal.click_collect.getCurrentPosition(Drupal.pdp.LocationSuccess, Drupal.pdp.LocationError);
        }
      });

      // Hit the search store button on hitting enter when on textbox.
      $('.click-collect-form').once('prevent-enter').on('keypress', '.store-location-input', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
          e.preventDefault();
          if (!$.isEmptyObject(asCoords)) {
            $('.click-collect-form').find('.search-stores-button').click();
          }
          return false;
        }
      });

      $('.click-collect-top-stores', context).once('bind-events').on('click', '.other-stores-link', function () {
        if ($(window).width() >= 768) {
          $('.click-collect-all-stores').toggleClass('desc-open', function () {
            // Scroll
            $('html,body').animate({
              scrollTop: $('.click-collect-all-stores').offset().top
            }, 'slow');
          });
        }
        else {
          $('.click-collect-all-stores').toggleClass('desc-open');
          $('#pdp-stores-container').accordion({
            active: false
          });
        }
      });

      $('.click-collect-all-stores', context).once('bind-events').on('click', '.close-inline-modal, .change-location-link, .search-stores-button, .cancel-change-location', function (e) {
        if (e.target.className === 'change-location-link') {
          Drupal.pdp.allStoresAutocomplete();
          Drupal.pdp.allStoreschangeLocationAutocomplete();
          $(this).siblings('.change-location').show();
          $(this).hide();
        }
        else if (e.target.className === 'cancel-change-location') {
          e.preventDefault();
          $(this).parent().hide();
          $('.click-collect-all-stores').find('.available-store-text .change-location-link').show();
          return false;
        }
        else if (e.target.className === 'search-stores-button' && !records) {
          e.preventDefault();
          Drupal.pdp.storesDisplay();
          return false;
        }
        else {
          $('.click-collect-all-stores').toggleClass('desc-open');
        }
      });

      $(document).on('click', function (e) {
        if ($(e.target).closest('.c-pdp .content__sidebar').length === 0 && $('.click-collect-all-stores').hasClass('desc-open')) {
          $('.click-collect-all-stores').removeClass('desc-open');
        }
      });

      $('.click-collect-form', context).once('bind-events').on('click', '.change-location-link, .search-stores-button, .cancel-change-location', function (e) {
        if (e.target.className === 'change-location-link') {
          $(this).siblings('.change-location').show();
          Drupal.pdp.changeLocationAutocomplete();
          $(this).hide();
        }
        else if (e.target.className === 'cancel-change-location') {
          e.preventDefault();
          $(this).parent().hide();
          $('.click-collect-form').find('.available-store-text .change-location-link').show();
          return false;
        }
        else if (e.target.className === 'search-stores-button' && !records) {
          e.preventDefault();
          Drupal.pdp.storesDisplay();
          return false;
        }
      });

      // Display search store form if conditions matched.
      Drupal.pdp.InvokeSearchStoreFormDisplay(settings);
    }
  };

  // Error callback.
  Drupal.pdp.LocationError = function (error) {
    geoPerm = false;
    // Display search store form if conditions matched.
    Drupal.pdp.InvokeSearchStoreFormDisplay(drupalSettings);
  };

  // Success callback.
  Drupal.pdp.LocationSuccess = function (position) {
    asCoords = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    };
    geoPerm = true;
    Drupal.pdp.storesDisplay(asCoords, $('.click-collect-form'));
  };

  // Set the location coordinates, but don't render the stores.
  Drupal.pdp.setStoreCoords = function (coords, field, restriction, $trigger) {
    asCoords = coords;
    if (!$.isEmptyObject(asCoords)) {
      Drupal.pdp.storesDisplay(asCoords);
    }
  };

  Drupal.pdp.getProductInfo = function () {
    // Get the SKU.
    var sku = $('#pdp-stores-container').data('sku');
    var skuClean = $('#pdp-stores-container').data('sku-clean');
    var variant = null;
    // Get the type.
    var type = $('#pdp-stores-container').data('product-type');
    if (type === 'configurable') {
      variant = $('.selected-variant-sku-' + skuClean).val();
    }

    return {
      type: type,
      sku: sku,
      selectedVariant: variant,
      skuClean: skuClean
    };
  };

  Drupal.pdp.changeLocationAutocomplete = function () {
    var field = $('.click-collect-form').find('input[name="store-location"]')[0];
    new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.storesDisplay]);
  };

  // Invoke display search store form if conditions matched.
  Drupal.pdp.InvokeSearchStoreFormDisplay = function (settings) {
    // Validate the product is same on ajax call.
    var validateProduct = Drupal.pdp.validateCurrentProduct(settings);
    // Get the settings for search form display.
    displaySearchForm = settings.alshaya_click_collect.searchForm;

    // If geolocation permission is denied then display the search form.
    if (typeof geoPerm !== 'undefined' && !geoPerm && validateProduct && displaySearchForm) {
      Drupal.pdp.displaySearchStoreForm();
    }
  };

  // Display search store form.
  Drupal.pdp.displaySearchStoreForm = function () {
    var productInfo = Drupal.pdp.getProductInfo();
    var check = false;
    if (productInfo.type === 'configurable') {
      check = (productInfo.selectedVariant) ? productInfo.selectedVariant.length : false;
    }
    else {
      check = productInfo.sku.length;
    }

    if (check) {
      $('.click-collect-empty-selection').hide();
      $('.click-collect-form').show();
      $('.click-collect-form').find('.available-store-text').hide();
      $('.click-collect-form').find('.store-finder-form-wrapper').show();
    }
  };

  // Make Ajax call to get stores and render html.
  Drupal.pdp.storesDisplay = function (coords, field, restriction, $trigger) {
    if (coords) {
      asCoords = coords;
    }

    if (!$.isEmptyObject(asCoords)) {
      // Get the Product info.
      var productInfo = Drupal.pdp.getProductInfo();
      var sku = '';
      if (productInfo) {
        sku = productInfo.sku;
        if (productInfo.type === 'configurable') {
          if (typeof productInfo.selectedVariant !== 'undefined' && productInfo.selectedVariant !== null) {
            $('.click-collect-empty-selection').hide();
            sku = productInfo.selectedVariant;
          }
          else {
            $('.click-collect-empty-selection').show();
            $('.click-collect-form').hide();
            return;
          }
        }

        if (asCoords !== null) {
          var checkLocation = true;
          if (lastCoords !== null) {
            checkLocation = (lastCoords.lat !== asCoords.lat || lastCoords.lng !== asCoords.lng);
          }

          if ((lastSku === null || lastSku !== sku) || checkLocation) {
            lastSku = sku;
            lastCoords = asCoords;

            if (typeof $trigger === 'undefined') {
              $trigger = $('.click-collect-form');
            }

            // Add formatted address based on lat/lng before ajax for top three stores.
            Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-form').find('.google-store-location'));
            // Add formatted address based on lat/lng before ajax for all stores. If html elements available.
            if ($('.click-collect-all-stores').find('.google-store-location').length > 0) {
              Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-all-stores').find('.google-store-location'));
            }

            var storeDisplayAjax = Drupal.ajax({
              url: Drupal.url('stores/product/' + lastSku + '/' + asCoords.lat + '/' + asCoords.lng),
              element: $trigger.get(0),
              base: false,
              progress: {type: 'throbber'},
              submit: {js: true}
            });

            // Custom command function to render map and map markers.
            storeDisplayAjax.commands.storeDisplayFill = function (ajax, response, status) {
              if (status === 'success') {
                if (response.data.alshaya_click_collect.pdp.top_three) {
                  // Show formatted address after ajax for all stores, once we have html elements.
                  Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-all-stores').find('.google-store-location'));
                  displaySearchForm = false;
                  records = true;
                }
                else {
                  displaySearchForm = true;
                }
              }
            };
            storeDisplayAjax.execute();
          }
        }
      }
    }
  };

  // Make autocomplete field in search form in the all stores.
  Drupal.pdp.allStoresAutocomplete = function () {
    var field = $('#all-stores-search-store').find('input[name="location"]')[0];
    new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.storesDisplay], {}, $('.click-collect-all-stores').find('.store-finder-form-wrapper'));
  };

  // Make change location field autocomplete in All stores modal.
  Drupal.pdp.allStoreschangeLocationAutocomplete = function () {
    var field = $('.click-collect-all-stores').find('input[name="store-location"]')[0];
    new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.storesDisplay], {}, $('.click-collect-all-stores').find('.store-finder-form-wrapper'));
  };

  /**
   * Validate the current product for ajax call.
   *
   * On pdp page when we open the cross sell product's modal window and
   * try to change the attributes for configurable product, it calls the
   * Drupal.pdp.storesDisplay() function, but for modal we don't want to
   * call this function. so we are checking here the size attribute that
   * is changed is for the same product.
   *
   * @param {Array} settings
   *  An array of settings that will be used.
   *
   * @return {boolean}
   *   Return true if both sku are same else false.
   */
  Drupal.pdp.validateCurrentProduct = function (settings) {
    var productInfo = Drupal.pdp.getProductInfo();
    var validate = true;
    if (typeof settings.alshaya_acm.product_sku !== 'undefined') {
      validate = (settings.alshaya_acm.product_sku === productInfo.sku);
    }
    return validate;
  };

  /**
   * Update click and collect on size change ajax call.
   *
   * We don't want to call this function for each size change call. we
   * want to call this function for pdp page only. so, there are some
   * variables which are accessible to javascript only. that's why we
   * are writing this function here instead of writing in AjaxResponse
   * of size change.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The Drupal Ajax object.
   * @param {object} response
   *   Object holding the server response.
   * @param {object} [response.settings]
   *   An array of settings that will be used.
   * @param {number} [status]
   *   The XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.updatePDPClickCollect = function (ajax, response, status) {
    if (Drupal.pdp.validateCurrentProduct(response.data)) {
      $('#pdp-stores-container.click-collect > h3 > .subtitle').text(response.data.alshaya_acm.subtitle_txt);
      var accordionStatus = $('#pdp-stores-container.click-collect').accordion('option', 'active');
      if (response.data.alshaya_acm.storeFinder) {
        if ($('#pdp-stores-container.click-collect').accordion('option', 'disabled')) {
          $('#pdp-stores-container.click-collect').accordion('option', 'disabled', false);
        }

        if (!accordionStatus) {
          $('#pdp-stores-container.click-collect').accordion('option', 'active', true);
        }
        Drupal.pdp.storesDisplay();
      }
      else {
        if (typeof accordionStatus === 'number' && accordionStatus === 0) {
          $('#pdp-stores-container.click-collect').accordion('option', 'active', false);
        }
        if (!$('#pdp-stores-container.click-collect').accordion('option', 'disabled')) {
          $('#pdp-stores-container.click-collect').accordion('option', 'disabled', true);
        }

      }
    }
  };

  // Command to display error message and rebind autocomplete to main input.
  $.fn.clickCollectPdpNoStoresFound = function (data) {
    $('.click-collect-top-stores').html(data);
    $('.click-collect-all-stores').html('');
    $('.click-collect-form .available-store-text').hide();
    $('.click-collect-form .change-location').hide();

    // Bind the js again to main input.
    var field = $('.click-collect-form').find('input[name="location"]')[0];
    new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.setStoreCoords]);

    $('.click-collect-form .store-finder-form-wrapper').show();
  };

})(jQuery, Drupal);
