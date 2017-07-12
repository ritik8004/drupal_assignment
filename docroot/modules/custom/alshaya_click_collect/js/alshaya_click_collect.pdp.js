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

  // Geolocation permission.
  var geoPerm = false;

  // Check records already exists.
  var records = false;

  // Display search form.
  var displaySearchForm;

  Drupal.pdp = Drupal.pdp || {};
  Drupal.geolocation = Drupal.geolocation || {};

  Drupal.behaviors.pdpClickCollect = {
    attach: function (context, settings) {
      if (typeof Drupal.geolocation.loadGoogle === 'function') {
        // First load the library from google.
        Drupal.geolocation.loadGoogle(function () {
          var field = $('.click-collect-form').find('input[name="location"]')[0];
          new Drupal.ClickCollect(field, [Drupal.pdp.setStoreCoords]);
        });
      }

      $('#pdp-stores-container', context).once('initiate-stores').each(function () {
        // Get the permission track the user location.
        Drupal.click_collect.getCurrentPosition(Drupal.pdp.LocationSuccess, Drupal.pdp.LocationError);

        // Check if we have to show the block as disabled. Since accordion classes
        // are added in JS, this is handled in JS.
        if ($(this).attr('state') === 'disabled') {
          $('#pdp-stores-container.click-collect > h3')
            .removeClass('ui-state-active ui-accordion-header-active')
            .addClass('ui-state-disabled');
          $('#pdp-stores-container.click-collect > .c-accordion_content').hide();
        }
        else {
          // Get the permission track the user location.
          Drupal.click_collect.getCurrentPosition(Drupal.pdp.LocationSuccess, Drupal.pdp.LocationError);
        }
      });

      $('.click-collect-top-stores', context).once('bind-events').on('click', '.other-stores-link', function () {

        $('.click-collect-all-stores').toggle('slow', function () {
          // Dispaly formatted address to make sure it has location.
          Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-all-stores').find('.google-store-location'));
          // Scroll
          $('html,body').animate({
            scrollTop: $('.click-collect-all-stores').offset().top
          }, 'slow');
        });

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
          $('.click-collect-all-stores').toggle('slow');
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

          asCoords = {
            lat: 29,
            lng: 48
          };

          Drupal.pdp.storesDisplay();
          return false;
        }
      });

      // Validate the product is same on ajax call.
      var validateProduct = Drupal.pdp.validateCurrentProduct(settings);
      // Call storesDisplay to render stores, if click and collect available for selected sku.
      if (settings.alshaya_acm.storeFinder === true && validateProduct && asCoords !== null) {
        Drupal.pdp.storesDisplay();
      }

      if (typeof displaySearchForm === 'undefined') {
        displaySearchForm = settings.alshaya_acm.searchForm;
      }
      // If geolocation permission is denied then display the search form.
      if (!geoPerm && validateProduct && displaySearchForm) {
        Drupal.pdp.dispalySearchStoreForm();
      }

      // Dispaly formatted address once we have store list.
      if (settings.alshaya_click_collect.pdp.ajax_call) {
        Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-form').find('.google-store-location'));
        Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-all-stores').find('.google-store-location'));
      }
    }
  };

  // Error callback.
  Drupal.pdp.LocationError = function (error) {
    geoPerm = false;
    // Do nothing, we already have the search form displayed by default.
  };

  // Success callback.
  Drupal.pdp.LocationSuccess = function (position) {
    asCoords = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    };
    geoPerm = true;
    Drupal.pdp.storesDisplay();
  };

  // Set the location coordinates, but don't render the stores.
  Drupal.pdp.setStoreCoords = function () {
    asCoords = this;

    if (records) {
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
    new Drupal.ClickCollect(field, [Drupal.pdp.storesDisplay]);
  };

  // Dispaly search store form.
  Drupal.pdp.dispalySearchStoreForm = function () {
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
      $('.click-collect-form').find('.store-finder-form-wrapper .search-store').show();
    }
  };

  // Make Ajax call to get stores and render html.
  Drupal.pdp.storesDisplay = function (coords) {
    if (typeof this.lat !== 'undefined' && typeof coords === 'undefined') {
      asCoords = this;
    }
    else if (coords) {
      asCoords = coords;
    }

    if (asCoords) {
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

            var storeDisplayAjax = Drupal.ajax({
              url: Drupal.url('stores/product/' + lastSku + '/' + asCoords.lat + '/' + asCoords.lng),
              element: $('.click-collect-form').get(0),
              base: false,
              progress: {type: 'throbber'},
              submit: {js: true}
            });

            // Custom command function to render map and map markers.
            storeDisplayAjax.commands.storeDisplayFill = function (ajax, response, status) {
              if (status === 'success') {
                if (response.data.top_three) {
                  displaySearchForm = false;
                  records = true;
                  Drupal.pdp.changeLocationAutocomplete();
                  if (response.data.all_stores) {
                    Drupal.pdp.allStoresAutocomplete();
                    Drupal.pdp.allStoreschangeLocationAutocomplete();
                  }
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
    new Drupal.ClickCollect(field, [Drupal.pdp.storesDisplay]);
  };

  // Make change location field autocomplete in All stores modal.
  Drupal.pdp.allStoreschangeLocationAutocomplete = function () {
    var field = $('.click-collect-all-stores').find('input[name="store-location"]')[0];
    new Drupal.ClickCollect(field, [Drupal.pdp.storesDisplay]);
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
      if (response.data.alshaya_acm.storeFinder) {
        $('#pdp-stores-container.click-collect > h3 > .subtitle').text(response.data.alshaya_acm.subtitle_txt);
        $('#pdp-stores-container.click-collect > h3')
          .removeClass('ui-state-disabled')
          .addClass('ui-state-active ui-accordion-header-active');
        $('#pdp-stores-container.click-collect > .c-accordion_content').show();

        Drupal.pdp.storesDisplay();
      }
      else {
        $('#pdp-stores-container.click-collect > h3 > .subtitle').text(response.data.alshaya_acm.subtitle_txt);
        $('#pdp-stores-container.click-collect > h3')
          .removeClass('ui-state-active ui-accordion-header-active')
          .addClass('ui-state-disabled');
        $('#pdp-stores-container.click-collect > .c-accordion_content').hide();
      }
    }
  };

})(jQuery, Drupal);
