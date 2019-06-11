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

})(jQuery, Drupal);
