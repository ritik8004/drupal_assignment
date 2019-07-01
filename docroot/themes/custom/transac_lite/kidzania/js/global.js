/**
 * @file
 * Globaly required scripts.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.menuToggle = {
    attach: function () {
      $('.menu-toggle').on('click', function (e) {
        $('.menu-navigation').toggleClass('show-menu');
        e.preventDefault();
      });
      // Delete local storage after booking complete.
      if (drupalSettings.clear_storage) {
        localStorage.removeItem('booking_info');
      }
      // Get booking info from local storage.
      if (localStorage.getItem('booking_info') !== null) {
        var getDataFromLocal = JSON.parse(localStorage.getItem('booking_info'));
        $('.visit-date').html(getDataFromLocal.visit_date);
        $('.order-total, .path--payment .total-price').html(getDataFromLocal.total.price);
        $('#booking-info').val(JSON.stringify(getDataFromLocal));
      }
    }
  };

  Drupal.behaviors.mobilenoValidation = {
    attach: function () {
      $('#booking-payment-form').on('submit', function () {
        $('.local-number').addClass('required');
      });
    }
  };

  var supportsES6 = function () {
    try {
      new Function('(a = 0) => a');
      return true;
    }
    catch (err) {
      return false;
    }
  }();

  if (!supportsES6) {
    $('.IESupportMsg').show();
  }
})(jQuery, Drupal, drupalSettings);
