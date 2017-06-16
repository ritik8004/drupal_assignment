/**
 * @file
 * Size and Color Guide js.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sizeguide = {
    attach: function (context, settings) {
      // JS for converting select list for size to unformatted list on PDP pages.
      function select2OptionConvert() {
        if ($(window).width() > 1024) {
          if ($('.acq-content-product .form-item-configurables-size .select2Option').length === 0) {
            $('.acq-content-product .form-item-configurables-size select').select2Option();
          }
          if ($('.acq-content-product-modal .form-item-configurables-size .select2Option').length === 0) {
            $('.acq-content-product-modal .form-item-configurables-size select').select2Option();
          }
        }
      }

      // Show mobile slider only on mobile resolution.
      select2OptionConvert();
      $(window).on('resize', function (e) {
        select2OptionConvert();
      });

      // JS for converting select list for size to unformatted list on PDP pages.
      $('.acq-content-product .form-item-configurables-size, .acq-content-product-modal .form-item-configurables-size').once('bind-events').each(function () {
        $('select', $(this)).select2Option();

        var clickedOption = $('.select2Option li a.picked', $(this));
        $('.select2Option', $(this)).find('.list-title .selected-text').remove();
        $('.select2Option', $(this)).find('.list-title').append('<span class="selected-text">' + clickedOption.text() + '</span>');
      });
    }
  };

})(jQuery, Drupal);
