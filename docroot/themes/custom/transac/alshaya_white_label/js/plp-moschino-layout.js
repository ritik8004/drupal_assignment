/**
 * @file
 * Custom js for videos on PLP page.
 */

(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.alshayaPLPVideos = {
    attach: function (context, settings) {
      if ($('.plp-video').length > 1) {
        $('#plp-video-player').ready(function () {

          // Store the video object
          var plpPlayer = $(this);

          plpPlayer.play();

          // Set click functions.
          $('.video-js, .plp-mute-button').click(function () {
            if (plpPlayer.muted()) {
              plpPlayer.muted(false);
              $('.plp-mute-button').removeClass('plp-video-muted');
            }
            else {
              plpPlayer.muted(true);
              $('.plp-mute-button').addClass('plp-video-muted');
            }
          });
        });
      }

      // Accordion for submenu links.
      $('.field--name-field-plp-menu').find('.mos-menu-item').each(function () {
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
      $('.moschino-layout-submenu-icon .sub-menu-btn').on('click', function () {
        $('.moschino-sub-menu-content').toggle();
      });

      $('.moschino-sub-menu-content .close-btn').on('click', function () {
        $('.moschino-sub-menu-content').toggle();
      });
    }
  };
})(jQuery, Drupal);
