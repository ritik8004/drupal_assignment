/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.joinusblock = {
    attach: function (context, settings) {
      if ($('#block-alshaya-white-label-content div').hasClass('joinclub')) {
        $('#block-alshaya-white-label-content article').addClass('joinclubblock');
      }

      $('.read-more-description-link').on('click', function () {
        $(window).scrollTop(0);
      });

      $('.other-stores-link').on('click', function () {
        $(window).scrollTop(0);
      });
    }
  };

})(jQuery, Drupal);
