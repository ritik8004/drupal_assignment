(function ($, Drupal) {
  'use strict';

  $.fn.correctFloorFieldLabel = function () {
    if ($('.form-item-field-address-floor').length) {
      var label = $('.form-item-field-address-floor').parent().find('label:not(".error")');
      label.addClass('active-label');
    }
  };

})(jQuery, Drupal);
