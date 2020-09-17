/**
 * @file
 * Aura Header JS file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.auraHeaderPopup = {
    attach: function (context) {
      if ($(window).width() > 1024) {
        $('.logged-out .aura-header-link a', context).on('click', function (e) {
          e.preventDefault();
          $('.logged-out .aura-header-popup-wrapper').toggle();
          e.stopPropagation();
        });

        $(document, context).on('click', function (e) {
          var displayState = $('.logged-out .aura-header-popup-wrapper').css('display');

          if (displayState !== 'none') {
            if (!($(e.target).closest('.aura-header-popup-wrapper').length)) {
              $('.logged-out .aura-header-popup-wrapper').hide();
            }
          }
        });
      }
    }
  };
})(jQuery, Drupal)
