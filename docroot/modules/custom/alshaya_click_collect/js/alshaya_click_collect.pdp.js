/**
 * @file
 * Store Finder - PDP.
 */

(function ($, Drupal, drupalSettings) {

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
      // We wait for rcs to do the replacement of tokens and then only we allow
      // the events to be attached.
      var node = $('.sku-base-form').not('[data-sku *= "#"]').closest('article.entity--type-node').first();

      $('body').once('click-collect').on('variant-selected', '.sku-base-form', function (event, variant, code) {
        var sku = $(this).attr('data-sku');
        var productKey = Drupal.getProductKeyForProductViewMode(node.attr('data-vmode'));
        var productInfo = window.commerceBackend.getProductData(sku, productKey);

        if (typeof productInfo === 'undefined' || !productInfo) {
          return;
        }

        var variantInfo = productInfo['variants'][variant];
        // Return if variant data not available.
        if (typeof variantInfo === 'undefined') {
          return;
        }

        $('#pdp-stores-container', node).data('sku', variant);

        if (variantInfo.click_collect) {
          $('#pdp-stores-container.click-collect', node).accordion('option', 'disabled', false);
          $('#pdp-stores-container.click-collect .c-accordion_content', node).removeClass('hidden-important');

          if ($('.search-stores-button', node).is(':visible')) {
            Drupal.pdp.storesDisplay(node);
          }
        }
        else {
          $('#pdp-stores-container.click-collect', node).accordion('option', 'disabled', true);
          $('#pdp-stores-container.click-collect .c-accordion_content', node).addClass('hidden-important');
        }
      });

      $('#pdp-stores-container', node).once('bind-js').each(function () {
        // Check if we have to show the block as disabled. Since accordion classes
        // are added in JS, this is handled in JS.
        if ($(this).data('state') === 'disabled') {
          $('#pdp-stores-container.click-collect .c-accordion_content', node).addClass('hidden-important');
          if ($('#pdp-stores-container.click-collect', node).accordion()) {
            $(this).accordion('option', 'disabled', true);
          }
        }
      });

      $('#pdp-stores-container h3', node).once('bind-js').on('click', function () {
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

        $('#pdp-stores-container', node).once('initiate-stores').each(function () {
          // Check if we have access to click & collect.
          if ($(this).data('state') !== 'disabled') {
            // Get the permission track the user location.
            $(this).on('click', function () {
              // Do a check if the library is already loaded.
              if ($(this).hasClass('maps-loaded')) {
                return;
              }

              // Add a check for identify that library is loaded.
              $(this).addClass('maps-loaded');

              // First load the library from google.
              Drupal.geolocation.loadGoogle(function () {
                Drupal.click_collect.getCurrentPosition(Drupal.click_collect.LocationSuccess, Drupal.click_collect.LocationError);

                // Try again if we were not able to get location on page load.
                if (geoPerm === false && typeof $('#pdp-stores-container', node).data('second-try') === 'undefined') {
                  $('#pdp-stores-container', node).data('second-try', 'done');
                  Drupal.click_collect.getCurrentPosition(Drupal.click_collect.LocationSuccess, Drupal.click_collect.LocationError);
                }
              });

              // Get the input field element.
              var field = $('.click-collect-form', node).find('input[name="location"]')[0];
              $(field).once('autocomplete-init').on('keyup keypress', function (e) {
                // If the input field length is 2 or more, we will load the
                // google library if not loaded already.
                if ($(this).val().length >= 2) {
                  // Do a check if the autocomplete is already attached with field.
                  if ($('#pdp-stores-container', node).hasClass('autocomplete-processed')) {
                    return;
                  }

                  // Add a check for identify that library is loaded.
                  $('#pdp-stores-container', node).addClass('autocomplete-processed');

                  new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.setStoreCoords], { 'country': settings.alshaya_click_collect.country.toLowerCase() });
                } else {
                  // If library is loaded and character length is found under
                  // 3 character, we will clear the events binds with the input.
                  if ($('#pdp-stores-container', node).hasClass('autocomplete-processed')) {
                    $(".pac-container").remove();
                    google.maps.event.clearInstanceListeners(field);
                    $('#pdp-stores-container', node).removeClass('autocomplete-processed');
                  }
                }
              });
            });
          }
        });
      });

      $('.click-collect-top-stores', node).once('bind-events').on('click', '.other-stores-link', function () {
        if ($(window).width() >= 768) {
          $('.click-collect-all-stores.inline-modal-wrapper', node).append('<div class="gradient-holder"></div>');
          // Close read more description window if open.
          if ($('.c-pdp .description-wrapper').hasClass('desc-open')) {
            $('.c-pdp .description-wrapper').toggleClass('desc-open');
          }
          $('.click-collect-all-stores', node).toggleClass('desc-open', function () {
            // Scroll.
            $('html,body').animate({
              scrollTop: 0
            }, 'slow');
            $('#pdp-stores-container', node).accordion({
              active: false
            });
          });
        }
        else {
          $('.click-collect-all-stores', node).slideToggle('slow');
        }
      });

      $('.click-collect-all-stores', node).once('bind-events').on('click', '.close-inline-modal, .change-location-link, .search-stores-button, .cancel-change-location', function (e) {
        if (e.target.className === 'change-location-link') {
          Drupal.pdp.allStoresAutocomplete(node);
          Drupal.pdp.allStoreschangeLocationAutocomplete(node);
          $(this).siblings('.change-location').show();
          $(this).hide();
        }
        else if (e.target.className === 'cancel-change-location') {
          e.preventDefault();
          $(this).parent().hide();
          $('.click-collect-all-stores', node).find('.available-store-text .change-location-link').show();
          return false;
        }
        else if (e.target.className === 'search-stores-button' && !records) {
          e.preventDefault();
          Drupal.pdp.storesDisplay(node);
          return false;
        }
        else {
          $('.click-collect-all-stores', node).toggleClass('desc-open');
        }
      });

      $(document).once().on('click', function (e) {
        if ($(e.target).closest('.c-pdp .content__sidebar').length === 0 && $('.click-collect-all-stores', node).hasClass('desc-open')) {
          $('.click-collect-all-stores', node).removeClass('desc-open');
        }
      });

      $('.click-collect-form', node).once('bind-events').on('click', '.change-location-link, .search-stores-button, .cancel-change-location', function (e) {
        if (e.target.className === 'change-location-link') {
          $(this).siblings('.change-location').show();
          Drupal.pdp.changeLocationAutocomplete(node);
          $(this).hide();
        }
        else if (e.target.className === 'cancel-change-location') {
          e.preventDefault();
          $(this).parent().hide();
          $('.click-collect-form', node).find('.available-store-text .change-location-link').show();
          return false;
        }
        else if (e.target.className === 'search-stores-button' && !records) {
          e.preventDefault();
          Drupal.pdp.storesDisplay(node);
          return false;
        }
      });

      // Display search store form if conditions matched.
      Drupal.pdp.InvokeSearchStoreFormDisplay(node, settings);
    }
  };

  // Error callback.
  Drupal.click_collect.LocationAccessError = function (drupalSettings) {
    geoPerm = false;

    var context = $('.entity--type-node #pdp-stores-container').closest('.entity--type-node');
    // Display search store form if conditions matched.
    Drupal.pdp.InvokeSearchStoreFormDisplay(context, drupalSettings);
  };

  // Success callback.
  Drupal.click_collect.LocationAccessSuccess = function (coords, context) {
    var context = $('#pdp-stores-container').closest('article.entity--type-node');
    asCoords = coords;
    geoPerm = true;
    Drupal.pdp.storesDisplay(context, asCoords, $('.click-collect-form', context));
  };

  // Set the location coordinates, but don't render the stores.
  Drupal.pdp.setStoreCoords = function (context, coords, field, restriction, $trigger) {
    asCoords = coords;
    if (!$.isEmptyObject(asCoords)) {
      Drupal.pdp.storesDisplay(context, asCoords);
    }
  };

  Drupal.pdp.getProductInfo = function (context) {
    // Get the SKU.
    var sku = $('#pdp-stores-container', context).data('sku');
    var skuClean = $('#pdp-stores-container', context).data('sku-clean');
    var variant = null;
    // Get the type.
    var type = $('#pdp-stores-container', context).data('product-type');
    if (type === 'configurable') {
      variant = $('.selected-variant-sku-' + skuClean, context).val();
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

  Drupal.pdp.changeLocationAutocomplete = function (context) {
    var field = $('.click-collect-form', context).find('input[name="store-location"]')[0];
    var restriction = {'country': drupalSettings.alshaya_click_collect.country.toLowerCase()};
    var callbacks = [Drupal.pdp.storesDisplay];
    new Drupal.AlshayaPlacesAutocomplete(field, callbacks, restriction, null, context);
    // Hit the search store button on hitting enter when on textbox.
    $('.click-collect-form', context).find('input[name="store-location"]').once('trigger-enter').on('keypress', function (e) {
      var keyCode = e.keyCode || e.which;
      if (keyCode === 13) {
        Drupal.AlshayaPlacesAutocomplete.handleEnterKeyPress($(this), callbacks, restriction);
      }
    });
  };

  // Invoke display search store form if conditions matched.
  Drupal.pdp.InvokeSearchStoreFormDisplay = function (context, settings) {
    // Do not process if context is empty.
    if (!context.length) {
      return;
    }
    // Validate the product is same on ajax call.
    var validateProduct = Drupal.pdp.validateCurrentProduct(settings, context);
    // Get the settings for search form display.
    displaySearchForm = settings.alshaya_click_collect.searchForm;

    // If geolocation permission is denied then display the search form.
    if (typeof geoPerm !== 'undefined' && !geoPerm && validateProduct && displaySearchForm) {
      Drupal.pdp.displaySearchStoreForm(context);
    }
  };

  // Display search store form.
  Drupal.pdp.displaySearchStoreForm = function (context) {
    var productInfo = Drupal.pdp.getProductInfo(context);
    var check = false;

    if (typeof productInfo.type === 'undefined') {
      // Do nothing. "check" will remain false.
    }
    else if (productInfo.type === 'configurable') {
      check = (productInfo.selectedVariant) ? productInfo.selectedVariant.length : false;
    }
    else {
      check = productInfo.sku.length;
    }

    if (check) {
      $('.click-collect-empty-selection', context).hide();
      $('.click-collect-form', context).show();
      $('.click-collect-form', context).find('.available-store-text').hide();
      $('.click-collect-form', context).find('.store-finder-form-wrapper').show();
    }
  };

  // Make Ajax call to get stores and render html.
  Drupal.pdp.storesDisplay = function (context, coords, field, restriction, $trigger) {
    var wait_for_maps_api = setInterval(function () {
      if (Drupal.geolocation.maps_api_loading === false) {
        clearInterval(wait_for_maps_api);
        if (coords) {
          asCoords = coords;
        }
        if (!$.isEmptyObject(asCoords)) {
          // Get the Product info.
          var productInfo = Drupal.pdp.getProductInfo(context);
          var sku = '';
          if (productInfo) {
            sku = productInfo.sku;
            if (productInfo.type === 'configurable') {
              if (typeof productInfo.selectedVariant !== 'undefined' && productInfo.selectedVariant !== null) {
                $('.click-collect-empty-selection', context).hide();
                sku = productInfo.selectedVariant;
              }
              else {
                $('.click-collect-empty-selection', context).show();
                $('.click-collect-form', context).hide();
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
                  $trigger = $('.click-collect-form', context);
                }

                // Add formatted address based on lat/lng before ajax for top three stores.
                Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-form', context).find('.google-store-location'), 'html');
                // Add formatted address based on lat/lng before ajax for all stores. If html elements available.
                if ($('.click-collect-all-stores', context).find('.google-store-location').length > 0) {
                  Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-all-stores', context).find('.google-store-location'), 'html');
                }

                var storeDisplayAjax = Drupal.ajax({
                  url: Drupal.url('stores/product/' + btoa(lastSku) + '/' + asCoords.lat + '/' + asCoords.lng),
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
                      Drupal.click_collect.getFormattedAddress(asCoords, $('.click-collect-all-stores', context).find('.google-store-location'), 'html');
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
  Drupal.pdp.allStoresAutocomplete = function (context) {
    var field = $('#all-stores-search-store', context).find('input[name="location"]')[0];
    new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.storesDisplay], {'country': drupalSettings.alshaya_click_collect.country.toLowerCase()}, $('.click-collect-all-stores', context).find('.store-finder-form-wrapper'), context);
  };

  // Make change location field autocomplete in All stores modal.
  Drupal.pdp.allStoreschangeLocationAutocomplete = function (context) {
    var field = $('.click-collect-all-stores', context).find('input[name="store-location"]')[0];
    new Drupal.AlshayaPlacesAutocomplete(field, [Drupal.pdp.storesDisplay], {'country': drupalSettings.alshaya_click_collect.country.toLowerCase()}, $('.click-collect-all-stores', context).find('.store-finder-form-wrapper'), context);
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
  Drupal.pdp.validateCurrentProduct = function (settings, context) {
    var productInfo = Drupal.pdp.getProductInfo(context);
    var validate = true;
    if (typeof settings.alshaya_acm.product_sku !== 'undefined') {
      validate = (settings.alshaya_acm.product_sku === productInfo.sku);
    }
    return validate;
  };

  // Command to display error message and rebind autocomplete to main input.
  $.fn.clickCollectPdpNoStoresFound = function (data) {
    var context = $('#pdp-stores-container').closest('article.entity--type-node');

    $('.click-collect-top-stores', context).html(data);
    $('.click-collect-all-stores', context).html('');
    $('.click-collect-form .available-store-text', context).hide();
    $('.click-collect-form .change-location', context).hide();
    $('.click-collect-form .store-finder-form-wrapper', context).show();
  };

})(jQuery, Drupal, drupalSettings);
