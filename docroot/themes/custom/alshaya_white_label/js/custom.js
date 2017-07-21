/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {
  'use strict';

  $(window).bind('pageshow', function (event) {
    if (event.originalEvent.persisted) {
      window.location.reload();
    }

    if ($('html').attr('dir') === 'rtl') {
      $('body').scrollTop(12);
      $('body').scrollTop(0);
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

      // Scrolling the page to top if edit address is clicked.
      $('.address .address--edit a').on('click', function () {
        $('html,body').animate({
          scrollTop: 0
        }, 'slow');
      });

      // Mobile grey block hiding over the image after 3secs.
      $('.mobilegallery .subtext').show().delay(3000).fadeOut();
    }
  };

  Drupal.behaviors.pdpModal = {
    attach: function (context, settings) {
      function modalOverlay(button, className) {
        $(button).click(function () {
          $('body').removeClass(className);
        });
      }

      $('.nodetype--acq_product .above-mobile-block').click(function () {
        $('body').addClass('pdp-modal-overlay');

        $(document).ajaxComplete(function () {
          modalOverlay('.ui-dialog-titlebar-close', 'pdp-modal-overlay');
        });
      });
    }
  };

})(jQuery, Drupal);
