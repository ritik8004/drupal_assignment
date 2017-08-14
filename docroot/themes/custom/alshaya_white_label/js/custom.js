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
          else {
            $('html,body').animate({
              scrollTop: $('.content__title_wrapper').offset().top
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

      // This js is to remove the success message of newsletter subscription after 10 seconds.
      setTimeout(function () {
        $('.subscription-status .success').fadeOut();
      }, 10000);
    }
  };

  Drupal.behaviors.pdpModal = {
    attach: function (context, settings) {
      function modalOverlay(button, className) {
        $(button).click(function () {
          $('body').removeClass(className);
        });
      }

      $('.nodetype--acq_product .owl-item .above-mobile-block, .path--cart .owl-item .above-mobile-block, .acq-content-product .cloudzoom #cloud-zoom-wrap').click(function () {
        $('body').addClass('pdp-modal-overlay');
        modalOverlay('.ui-dialog-titlebar-close', 'pdp-modal-overlay');

        $(document).ajaxComplete(function () {
          modalOverlay('.ui-dialog-titlebar-close', 'pdp-modal-overlay');
        });
      });
    }
  };

})(jQuery, Drupal);
