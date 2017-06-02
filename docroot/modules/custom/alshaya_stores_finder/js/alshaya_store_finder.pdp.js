/**
 * @file
 * Store Finder - PDP.
 */

(function ($, Drupal) {
  'use strict';

  // Coordinates of the user's location.
  var asf_coords = null;

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
    }
  };

  Drupal.pdp_stores_display = function () {
    // Get the SKU.
    var sku = $('#pdp-stores-container').attr('sku');

    // Get the type.
    var type = $('#pdp-stores-container').attr('type');

    if (asf_coords !== null) {

      if (type === 'configurable') {
        // Check if we have options selected.
        return;
      }

      $.ajax({
        url: Drupal.url('stores/product/' + sku + '/' + asf_coords.latitude + '/' + asf_coords.longitude),
        success: function(response) {
          // Create a Drupal.Ajax object without associating an element, a
          // progress indicator or a URL.
          var ajax_object = Drupal.ajax({
            url: '',
            base: false,
            element: $('#pdp-stores-container'),
            progress: false
          });

          // WE simulate an AJAX response having arrived, and let the Ajax
          // system handle it.
          ajax_object.success(response, 'success');
        }
      });
    }
  }

  /**
   * Add new command for updating stores for product after attribute is selected.
   */
  Drupal.AjaxCommands.prototype.pdpUpdateStores = function (ajax, response, status) {
    $('#pdp-stores-container').attr('sku', response.sku);
    Drupal.pdp_stores_display();
  }

})(jQuery, Drupal);
