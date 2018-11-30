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

      /**
       * Helper function to remove classes from body when dailog is closed.
       *
       * @param {*} className
       * The classname to be removed from body.
       */
      function modalCloseBtnEvent(className) {
        $('.ui-dialog-titlebar-close').on('click', function () {
          $('body').removeClass(className);
        });
      }

      /**
       * Helper function to add classes to body when ajax dailog is opened.
       *
       * @param {*} ajaxString
       * The string fragment from AJAX URL which can help us
       * identify the AJAX request.
       * @param {*} className
       * The className to be added on body tag.
       */
      function modalClasses(ajaxString, className) {
        $(document).ajaxComplete(function (event, xhr, settings) {
          if (settings.url.indexOf(ajaxString) !== -1) {
            $('body').addClass(className);
          }
          modalCloseBtnEvent(className);
        });
      }

      $('.nodetype--acq_product .owl-carousel .above-mobile-block, .path--cart .owl-carousel .above-mobile-block').on('click', function () {
        modalClasses('product-quick-view', 'pdp-modal-overlay');
      });

      $('.size-guide-link').on('click', function () {
        modalClasses('size-guide', 'sizeguide-modal-overlay');
      });

      $('.free-gift-title a, .free-gift-image a, .path--cart #table-cart-items table tr td.name a').on('click', function () {
        $('body').addClass('free-gifts-modal-overlay');
        modalCloseBtnEvent('free-gifts-modal-overlay');

        $(document).ajaxComplete(function () {
          modalCloseBtnEvent('free-gifts-modal-overlay');
        });
      });

      var modal_overlay_class = ['pdp-modal-overlay', 'sizeguide-modal-overlay', 'free-gifts-modal-overlay', 'social-modal-overlay'];

      $(document).on('keyup', function (evt) {
        // Remove class when esc button is used to remove the overlay.
        if (evt.keyCode === 27) {
          for (var i = 0; i < modal_overlay_class.length; i++) {
            if ($('body').hasClass(modal_overlay_class[i])) {
              $('body').removeClass(modal_overlay_class[i]);
              i = modal_overlay_class.length;
            }
          }
        }
      });
    }
  };

  // Add loader on plp search page.
  Drupal.behaviors.facetSearchLoader = {
    attach: function (context, settings) {
      $(document).ajaxSend(function (event, jqxhr, settings) {
        if (settings.url.indexOf('facets-block') > -1) {
          if ($('.page-standard > .ajax-progress-fullscreen').length === 0) {
            $('.page-standard').append('<div class="ajax-progress ajax-progress-fullscreen"></div>');
          }
        }
      });
      $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.url.indexOf('facets-block') > -1) {
          $('div.ajax-progress-fullscreen').remove();
        }
      });
    }
  };

  // Add class to slug banner modal.
  Drupal.behaviors.slugBannerModal = {
    attach: function (context, settings) {
      $(document).on('mousedown', '.slug-banner-modal-link.use-ajax', function () {
        $(document).on('dialogopen', '.ui-dialog', function () {
          $(this).addClass('slug-banner-modal');
        });
      });

      // Remove the class when the modal is closed.
      $(document).on('dialogclose', '.ui-dialog', function () {
        $(this).removeClass('slug-banner-modal');
      });
    }
  };

})(jQuery, Drupal);
