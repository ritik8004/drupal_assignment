/**
 * @file
 * Store Finder - PDP.
 */

(function ($, Drupal) {
  'use strict';

  // Coordinates of the user's location.
  var asf_coords = null;

  // Last checked SKU (or variant SKU).
  var last_sku = null;

  Drupal.behaviors.store_finder_pdp = {
    attach: function (context, settings) {
      var pdpStoresErrorCallback = function (error) {
        // Do nothing, we already have the search form displayed by default.
      };


      // Success callback.
      var pdpStoresSuccessCallback = function (position) {
        asf_coords = position.coords;
        Drupal.pdp_stores_display();
      };

      // Error callback.
      $('#pdp-stores-container').once('initiate-stores').each(function () {
        try {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pdpStoresSuccessCallback, pdpStoresErrorCallback);
          }
        }
        catch (e) {
        }
      });

      $('.other-stores-link').once('bind-events').each(function () {
        $(this).bind('click', function () {
          $('.click-collect-all-stores').slideToggle();
        });
      });

      $('.close-inline-modal').once('bind-events').each(function () {
        $(this).bind('click', function () {
          $(this).parents('.inline-modal-wrapper:first').slideToggle();
        });
      });

      // Call here once to ensure we do it after changes in attribute selection.
      Drupal.pdp_stores_display();
    }
  };

  Drupal.pdp_stores_display = function () {
    // Get the SKU.
    var sku = $('#pdp-stores-container').attr('sku');
    var sku_clean = $('#pdp-stores-container').attr('sku-clean');

    // Get the type.
    var type = $('#pdp-stores-container').attr('type');

    if (asf_coords !== null) {

      if (type === 'configurable') {
        if ($('.selected-variant-sku-' + sku_clean).length) {
          $('.click-collect-empty-selection').hide();
          sku = $('.selected-variant-sku-' + sku_clean).val();
        }
        else {
          $('.click-collect-empty-selection').show();
          $('.click-collect-form').hide();
          return;
        }
      }

      if (last_sku === null || last_sku != sku) {
        last_sku = sku;

        $.ajax({
          url: Drupal.url('stores/product/' + sku + '/' + asf_coords.latitude + '/' + asf_coords.longitude),
          success: function (response) {
            // Create a Drupal.Ajax object without associating an element, a
            // progress indicator or a URL.
            var ajax_object = Drupal.ajax({
              url: '',
              base: false,
              element: false,
              progress: false
            });

            // WE simulate an AJAX response having arrived, and let the Ajax
            // system handle it.
            ajax_object.success(response, 'success');
          }
        });
      }
    }
  }

})(jQuery, Drupal);
