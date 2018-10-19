/**
 * @file
 * Custom js for videos on PLP page.
 */

(function ($, Drupal) {
  'use strict';

  /* global videojs */

  Drupal.behaviors.alshayaPLPVideos = {
    attach: function (context, settings) {
      if ($('.moschino-plp-layout .plp-video').length !== 0) {
        // Store the video object
        var plpPlayer = videojs('#plp-video-player');

        // If autoplay does not work by default, play the video programatically.
        var autoplay = drupalSettings.autoplay;
        if (typeof autoplay !== 'undefined' && autoplay === 1) {
          setTimeout(function(){ plpPlayer.play(); }, 3000);
        }

        // Set click functions.
        $('.video-js').on('click', function () {
          if (plpPlayer.muted()) {
            plpPlayer.muted(false);
          }
          else {
            plpPlayer.muted(true);
          }
        });
      }

      // Accordion for submenu links.
      $('.moschino-plp-layout .field--name-field-plp-menu').find('.mos-menu-item').each(function () {
        // Create accordion if the menu has sub links.
        if ($(this).find('.mos-menu-sublink').length !== 0) {
          $(this).once('accordion-init').accordion({
            heightStyle: 'content',
            collapsible: true,
            active: false
          });
        }
      });

      // Show the sub menu on click of the sub menu btn.
      $('.moschino-plp-layout .moschino-layout-submenu-icon .sub-menu-btn', context).on('click', function () {
        $('.moschino-sub-menu-content').toggleClass('visible');
      });

      $('.moschino-plp-layout .moschino-sub-menu-content .close-btn', context).on('click', function () {
        $('.moschino-sub-menu-content').toggleClass('visible');
      });

      // Add class if it is moschino modal.
      $(document).on('mousedown', '.moschino-modal-link.use-ajax', function () {
        $(document).on('dialogopen', '.ui-dialog', function () {
          $(this).addClass('moschino-modal');
        });
      });

      // Remove the class when the modal is closed.
      $(document).on('dialogclose', '.ui-dialog', function () {
        $(this).removeClass('moschino-modal');
      });
    }
  };
})(jQuery, Drupal);
