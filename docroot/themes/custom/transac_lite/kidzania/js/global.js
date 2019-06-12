/**
 * @file
 * Globaly required scripts.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.menuToggle = {
    attach: function () {
      $('.menu-toggle').on('click', function (e) {
        $('.menu-navigation').toggleClass('show-menu');
        e.preventDefault();
      });

      // Get booking info from local storage.
      if (localStorage.getItem('booking_info') !== null) {
        var getDataFromLocal = JSON.parse(localStorage.getItem('booking_info'));
        $('.visit-date').html(getDataFromLocal.visit_date);
        $('.order-total').html(getDataFromLocal.total.price);
        $('#booking-info').val(JSON.stringify(getDataFromLocal));
      }
    }
  };

  Drupal.behaviors.mobilenoValidation = {
    attach: function () {
      var field = $('#edit-mobile-mobile');

      function validateField() {
        $('.mobile-error').remove();
        field.removeClass('error');
        var mobile = $('.local-number').val();

        if (!mobile) {
          field.addClass('error');
          $('.local-number').after('<div class="mobile-error error">Please enter your This field.</div>');
        }
      }
      $('#booking-payment-form').on('submit', function () {
        validateField();
      });

      field.on('change', function () {
        validateField();
      });
    }
  };

})(jQuery, Drupal);
