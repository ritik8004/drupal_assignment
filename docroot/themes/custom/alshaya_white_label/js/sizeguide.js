/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sizeguide = {
    attach: function (context, settings) {
      // JS for converting select list for size to unformatted list on PDP pages.
      if ($('.acq-content-product .form-item-configurables-size .select2Option').length === 0) {
        $('.acq-content-product .form-item-configurables-size select').select2Option();
      }
    }
  };

  Drupal.behaviors.sizeguideclick = {
    attach: function (context, settings) {
      var clickedOption = $('.acq-content-product .form-item-configurables-size .select2Option li a.picked');
      $('.acq-content-product .form-item-configurables-size .select2Option').find('.list-title .selected-text').remove();
      $('.acq-content-product .form-item-configurables-size .select2Option').find('.list-title').append('<span class="selected-text">' + clickedOption.text() + '</span');
    }
  };

})(jQuery, Drupal);
