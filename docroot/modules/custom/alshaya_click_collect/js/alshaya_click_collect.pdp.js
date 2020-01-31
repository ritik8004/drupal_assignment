/**
 * @file
 * Store Finder - PDP.
 */

(function ($, Drupal, drupalSettings) {
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

  // Delete behavior from contrib module loading google map api on page load.
  delete Drupal.behaviors.geolocationGeocoderGoogleGeocodingApi;
  $(window).on('load', function () {
    delete Drupal.behaviors.geolocationGeocoderGoogleGeocodingApi;
  });

  Drupal.behaviors.pdpClickCollect = {
    attach: function (context, settings) {
      $('.sku-base-form').once('click-collect').on('variant-selected', function (event, variant, code) {
        var node = $(this).parents('article.entity--type-node:first');
        if (node.length < 1) {
          return;
        }

        var sku = $(this).attr('data-sku');
        var variantInfo = drupalSettings.productInfo[sku]['variants'][variant];
        $('#pdp-stores-container', node).data('sku', variant);

        if (variantInfo.click_collect) {
          $('#pdp-stores-container.click-collect', node).accordion('option', 'disabled', false);
          $('#pdp-stores-container.click-collect .c-accordion_content', node).removeClass('hidden-important');

          if ($('.search-stores-button', node).is(':visible')) {
            Drupal.pdp.storesDisplay();
          }
        }
        else {
          $('#pdp-stores-container.click-collect', node).accordion('option', 'disabled', true);
          $('#pdp-stores-container.click-collect .c-accordion_content', node).addClass('hidden-important');
        }
      });

      $('#pdp-stores-container').once('bind-js').each(function () {
        // Check if we have to show the block as disabled. Since accordion classes
        // are added in JS, this is handled in JS.
        if ($(this).data('state') === 'disabled') {
          $('#pdp-stores-container.click-collect .c-accordion_content').addClass('hidden-important');
          $('#pdp-stores-container.click-collect').accordion('option', 'disabled', true);
        }
      });

      $('#pdp-stores-container h3').once('bind-js').on('click', function () {
        if (typeof Drupal.geolocation.loadGoogle !== 'function') {
          return;
        }

        if ($(this).hasClass('location-js-initiated')) {
          return;
        }

        $(this).addClass('location-js-initiated');

        // Location search google autocomplete fix.
        $(window).on('scroll', function () {
          $('.pac-container:visible').hide();
        });

        $('#pdp-stores-container').once('initiate-stores').each(function () {
          // Check if we have access to click & collect.
          if ($(this).data('state') !== 'disabled') {
            // Get the permission track the user location.
            $('#pdp-stores-container').on('click', function () {
              if ($(this).hasClass('maps-loaded')) {
                return;
              }

              $(this).addClass('maps-loaded');

              // First load the library from google.
              Drupal.geolocation.loadGoogle(function () {
                var field = $('.click-collect-form').find('input[name="location"]')[0];
                new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.setStoreCoords], {'country': settings.alshaya_click_collect.country.toLowerCase()});

                Drupal.click_collect.getCurrentPosition(Drupal.click_collect.LocationSuccess, Drupal.click_collect.LocationError);

                // Try again if we were not able to get location on page load.
                if (geoPerm === false && typeof $('#pdp-stores-container').data('second-try') === 'undefined') {
                  $('#pdp-stores-container').data('second-try', 'done');
                  Drupal.click_collect.getCurrentPosition(Drupal.click_collect.LocationSuccess, Drupal.click_collect.LocationError);
                }
              });
            });
          }
        });
      });

      $('.click-collect-top-stores', context).once('bind-events').on('click', '.other-stores-link', function () {
        if ($(window).width() >= 768) {
          $('.click-collect-all-stores.inline-modal-wrapper').append('<div class="gradient-holder"></div>');
          // Close read more description window if open.
          if ($('.c-pdp .description-wrapper').hasClass('desc-open')) {
            $('.c-pdp .description-wrapper').toggleClass('desc-open');
          }
          $('.click-collect-all-stores').toggleClass('desc-open', function () {
            // Scroll.
            $('html,body').animate({
              scrollTop: 0
            }, 'slow');
            $('#pdp-stores-container').accordion({
              active: false
            });
          });
        }
        else {
          $('.click-collect-all-stores').slideToggle('slow');
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

      $(document).once().on('click', function (e) {
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
  Drupal.click_collect.LocationAccessError = function (drupalSettings) {
    geoPerm = false;
    // Display search store form if conditions matched.
    Drupal.pdp.InvokeSearchStoreFormDisplay(drupalSettings);
  };

  // Success callback.
  Drupal.click_collect.LocationAccessSuccess = function (coords) {
    asCoords = coords;
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

    // We always use SKU as string. For numeric values JS converts it to int.
    // This fails our === condition checks.
    sku = (typeof sku === 'undefined') ? '' : sku.toString();
    skuClean = (typeof skuClean === 'undefined') ? '' : skuClean.toString();

    return {
      type: type,
      sku: sku,
      selectedVariant: variant,
      skuClean: skuClean
    };
  };

  Drupal.pdp.changeLocationAutocomplete = function () {
    var field = $('.click-collect-form').find('input[name="store-location"]')[0];
    var restriction = {'country': drupalSettings.alshaya_click_collect.country.toLowerCase()};
    var callbacks = [Drupal.pdp.storesDisplay];
    new Drupal.AlshayaPlacesAutocomplete(field, callbacks, restriction);
    // Hit the search store button on hitting enter when on textbox.
    $('.click-collect-form').find('input[name="store-location"]').once('trigger-enter').on('keypress', function (e) {
      var keyCode = e.keyCode || e.which;
      if (keyCode === 13) {
        Drupal.AlshayaPlacesAutocomplete.handleEnterKeyPress($(this), callbacks, restriction);
      }
    });
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
    var wait_for_maps_api = setInterval(function () {
      if (Drupal.geolocation.maps_api_loading === false) {
        clearInterval(wait_for_maps_api);
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

                if (typeof $trigger === 'undefined' || $trigger == null) {
                  $trigger = $('.click-collect-form');
                }

                // Add formatted address based on lat/lng before ajax for top three stores.
                Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-form').find('.google-store-location'), 'html');
                // Add formatted address based on lat/lng before ajax for all stores. If html elements available.
                if ($('.click-collect-all-stores').find('.google-store-location').length > 0) {
                  Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-all-stores').find('.google-store-location'), 'html');
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
                  if ($('body').hasClass('magazine-layout-ajax-throbber')) {
                    $('body').removeClass('magazine-layout-ajax-throbber');
                  }

                  if (status === 'success') {
                    if (response.data.alshaya_click_collect.pdp.top_three) {
                      // Show formatted address after ajax for all stores, once we have html elements.
                      Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-all-stores').find('.google-store-location'), 'html');
                      displaySearchForm = false;
                      records = true;
                    }
                    else {
                      displaySearchForm = true;
                    }
                  }
                };

                // When sidebar is sticky for magazine layout.
                if ($('.content-sidebar-wrapper').hasClass('sidebar-fixed')) {
                  $('body').addClass('magazine-layout-ajax-throbber');
                }

                storeDisplayAjax.execute();
              }
            }
          }
        }
      }
    }, 100);
  };

  // Make autocomplete field in search form in the all stores.
  Drupal.pdp.allStoresAutocomplete = function () {
    var field = $('#all-stores-search-store').find('input[name="location"]')[0];
    new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.storesDisplay], {'country': drupalSettings.alshaya_click_collect.country.toLowerCase()}, $('.click-collect-all-stores').find('.store-finder-form-wrapper'));
  };

  // Make change location field autocomplete in All stores modal.
  Drupal.pdp.allStoreschangeLocationAutocomplete = function () {
    var field = $('.click-collect-all-stores').find('input[name="store-location"]')[0];
    new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.storesDisplay], {'country': drupalSettings.alshaya_click_collect.country.toLowerCase()}, $('.click-collect-all-stores').find('.store-finder-form-wrapper'));
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

  // Command to display error message and rebind autocomplete to main input.
  $.fn.clickCollectPdpNoStoresFound = function (data) {
    $('.click-collect-top-stores').html(data);
    $('.click-collect-all-stores').html('');
    $('.click-collect-form .available-store-text').hide();
    $('.click-collect-form .change-location').hide();

    // Bind the js again to main input.
    var field = $('.click-collect-form').find('input[name="location"]')[0];
    new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.setStoreCoords], {'country': drupalSettings.alshaya_click_collect.country.toLowerCase()});

    $('.click-collect-form .store-finder-form-wrapper').show();
  };

})(jQuery, Drupal, drupalSettings);
