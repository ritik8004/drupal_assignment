/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {
  'use strict';

  $(window).bind('pageshow', function (event) {
    if (event.originalEvent.persisted) {
      window.location.reload()
    }
  });

  Drupal.behaviors.joinusblock = {
    attach: function (context, settings) {
      if ($('#block-alshaya-white-label-content div').hasClass('joinclub')) {
        $('#block-alshaya-white-label-content article').addClass('joinclubblock');
      }

      var mobileStickyHeaderHeight = $('.branding__menu').height();
      var normalStickyHeaderHeight = $('.branding__menu').height() + $('.header--wrapper').height();
      $('.read-more-description-link').on('click', function () {
        if ($(window).width() < 768) {
          $('html,body').animate({
            scrollTop: $('.content__sidebar').offset().top - mobileStickyHeaderHeight
          }, 'slow');
        }
        else {
          if ($('body').hasClass('header--fixed')) {
            $('html,body').animate({
              scrollTop: $('.content__title_wrapper').offset().top - normalStickyHeaderHeight
            }, 'slow');
          }
        }
      });
      $('.other-stores-link').on('click', function () {
        if ($(window).width() < 768) {
          $('html,body').animate({
            scrollTop: $('.content__sidebar').offset().top - mobileStickyHeaderHeight
          }, 'slow');
        }
        else {
          if ($('body').hasClass('header--fixed')) {
            $('html,body').animate({
              scrollTop: $('.content__title_wrapper').offset().top - normalStickyHeaderHeight
            }, 'slow');
          }
        }
      });

      // Mobile grey block hiding over the image after 3secs.
      $('.mobilegallery .subtext').show().delay(3000).fadeOut();
    }
  };

})(jQuery, Drupal);
