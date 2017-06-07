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
        $('html,body').animate({
          scrollTop: $('.content__title_wrapper').offset().top - 160
        }, 'slow');
      });
      $('.other-stores-link').on('click', function () {
        $('html,body').animate({
          scrollTop: $('.content__title_wrapper').offset().top - 160
        }, 'slow');
      });
    }
  };

})(jQuery, Drupal);
