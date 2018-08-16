/**
 * @file
 * Custom js file.
 */

(function ($, Drupal) {
  'use strict';

  document.addEventListener('gesturestart', function (ee) {
    ee.preventDefault();
  });

  $(window).on('pageshow', function (event) {
    if (event.originalEvent.persisted) {
      window.location.reload();
    }

    if ($('html').attr('dir') === 'rtl') {
      $('body').scrollTop(12);
      $('body').scrollTop(0);
    }
  });

  // Hide the current language link in language switcher block.
  // Try to do this as early as possible during page load.
  try {
    var currentLang = $('html').attr('lang');
    $('.header--wrapper .language-switcher-language-url .language-link[hreflang="' + currentLang + '"]').parent().addClass('hidden-important');
  }
  catch (e) {
    // Do nothing here.
  }

  // Adding class at field-promo-block level to differentiate department page drop down.
  $('.c-accordion-delivery-options').parent().parent().addClass('field--name-field-promo-block-accordion-delivery-options');

  Drupal.behaviors.removeContentLoadingThrobber = {
    attach: function (context, settings) {
      setTimeout(function () {
        jQuery('.show-content-loading-throbber').removeClass('show-content-loading-throbber');
      }, 100);
    }
  };

  Drupal.behaviors.joinusblock = {
    attach: function (context, settings) {
      if ($('#block-content div').hasClass('joinclub')) {
        $('#block-content article').addClass('joinclubblock');
      }

      var mobileStickyHeaderHeight = $('.branding__menu').height();

      function stopScrollEvents($el) {
        $($el).on('scroll mousedown DOMMouseScroll mousewheel keyup', function () {
          $($el).stop();
        });
      }

      function unBindScrollEvents($el) {
        $($el).unbind('scroll mousedown DOMMouseScroll mousewheel keyup');
      }

      $('.select-size-text .highlight', context).once('toselectsize').each(function () {
        $(this).click(function () {
          stopScrollEvents('html, body');

          if ($(window).width() < 768) {
            $('html,body').animate({scrollTop: $('.content__sidebar').offset().top - mobileStickyHeaderHeight}, 1200, 'easeOutQuart', unBindScrollEvents('html, body'));
            return false;
          }
          else {
            $('html,body').animate({scrollTop: 0}, 1200, 'easeOutQuart', unBindScrollEvents('html, body'));
            return false;
          }
        });
      });

      $('.read-more-description-link', context).once('toselectsize').each(function () {
        $(this).click(function () {
          stopScrollEvents('html, body');

          if ($(window).width() < 768) {
            $('html,body').animate({scrollTop: $('.content__sidebar').offset().top - mobileStickyHeaderHeight}, 1200, 'easeOutQuart', unBindScrollEvents('html, body'));
            return false;
          }
          else {
            $('html,body').animate({scrollTop: 0}, 1200, 'easeOutQuart', unBindScrollEvents('html, body'));
            return false;
          }
        });
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

      $('.nodetype--acq_product .owl-carousel .above-mobile-block, .path--cart .owl-carousel .above-mobile-block').click(function () {
        $('body').addClass('pdp-modal-overlay');
        modalOverlay('.ui-dialog-titlebar-close', 'pdp-modal-overlay');

        $(document).ajaxComplete(function () {
          modalOverlay('.ui-dialog-titlebar-close', 'pdp-modal-overlay');
        });
      });

      $('.size-guide-link ').click(function () {
        $('body').addClass('sizeguide-modal-overlay');
        modalOverlay('.ui-dialog-titlebar-close', 'sizeguide-modal-overlay');

        $(document).ajaxComplete(function () {
          modalOverlay('.ui-dialog-titlebar-close', 'sizeguide-modal-overlay');
        });
      });

      $('.free-gift-title a, .free-gift-image a, .path--cart #table-cart-items table tr td.name a').click(function () {
        $('body').addClass('free-gifts-modal-overlay');
        modalOverlay('.ui-dialog-titlebar-close', 'free-gifts-modal-overlay');

        $(document).ajaxComplete(function () {
          modalOverlay('.ui-dialog-titlebar-close', 'free-gifts-modal-overlay');
        });
      });
    }
  };

})(jQuery, Drupal);
