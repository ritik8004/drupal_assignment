/**
 * @file
 * Globaly required scripts.
 */

(function ($, Drupal, drupalSettings) {

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
        $('.order-total, .path--payment .total-price').html(parseFloat(getDataFromLocal.total.price).toFixed(3));
        $('#booking-info').val(JSON.stringify(getDataFromLocal));
      }
    }
  };

  Drupal.behaviors.mobilenoValidation = {
    attach: function () {
      $('.local-number').attr('autocomplete', 'off');
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

  $('#booking-payment-form').on('keypress input', '#edit-name', function (e) {
    var $this = $(this);
    var val = $this.val().trim();
    var regex = new RegExp('^[a-zA-Z ]+$');
    if (e.type === 'keypress') {
      var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
      if (!regex.test(key)) {
        e.preventDefault();
      }
    }
    else {
      if (!val.match(regex)) {
        $this.val(val.replace(/[^A-Za-z ]/g, '').replace(/ {1,}/g, ' '));
      }
    }
  });
})(jQuery, Drupal, drupalSettings);
