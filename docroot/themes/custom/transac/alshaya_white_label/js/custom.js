/**
 * @file
 * Custom js file.
 */

/* global debounce */

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

      // If the product opens in modal,
      // populate the hidden field with correct context.
      if ($(context).filter('article[data-vmode="modal"]').length === 1) {
        $('.nodetype--acq_product #drupal-modal input.hidden-context, .path--cart #drupal-modal input.hidden-context').val('modal');
      }

      /**
       * Helper function to remove overlay classes from body when dialog is closed.
       *
       */
      function modalCloseBtnEvent() {
        $('.ui-dialog-titlebar-close').once().on('click', function () {
          // Remove the classes added for overlay having suffix '-overlay'.
          var bodyClasses = $('body').attr('class').split(' ');
          for (var i = bodyClasses.length - 1; i >= 0; i--) {
            if (bodyClasses[i].indexOf('-overlay') > -1) {
              $('body').removeClass(bodyClasses[i]);
            }
          }
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
          modalCloseBtnEvent();
        });
      }

      // On dialog close.
      $(window).on('dialog:afterclose', function (e, dialog, $element) {
        // If body has overlay class, remove it.
        if ($('body').hasClass('modal-overlay')) {
          $('body').removeClass('modal-overlay');
          // We have a menu timer with delay on desktop for body::before
          // transition, also some regions have differnet z-index.
          // This class holds the z-index consisitent till all animations are
          // over. Otherwise we get a step animation, where the opacity for
          // background closes at different times for differnet regions.
          // see _utils.scss for classes where this gets applied.
          setTimeout(function () {
            $('body').removeClass('reduce-zindex');
          }, 550);
        }
      });

      $('.payment-card--delete a').on('click', function () {
        $('body').addClass('reduce-zindex');
        $('body').addClass('modal-overlay');
      });

      $('.nodetype--acq_product .owl-carousel .above-mobile-block, .path--cart .owl-carousel .above-mobile-block').on('click', function () {
        modalClasses('product-quick-view', 'pdp-modal-overlay');
      });

      $('.size-guide-link').on('click', function () {
        modalClasses('size-guide', 'sizeguide-modal-overlay');
      });

      var modal_overlay_class = ['pdp-modal-overlay', 'sizeguide-modal-overlay', 'free-gifts-modal-overlay'];

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

  var ajaxRequest = XMLHttpRequest.prototype.open;
  var currentAJAXRequests = [];
  XMLHttpRequest.prototype.open = function (method, url) {
    currentAJAXRequests.push(url);
    ajaxRequest.apply(this, arguments);
  };

  // Add loader on plp search page.
  Drupal.behaviors.facetSearchLoader = {
    attach: function (context, settings) {
      var removeProgressBar = false;
      $(document).ajaxSend(function (event, jqxhr, settings) {
        if (settings.url.indexOf('facets-block') > -1) {
          removeProgressBar = true;
          if ($('.page-standard > .ajax-progress-fullscreen').length === 0) {
            $('.page-standard').append('<div class="ajax-progress ajax-progress-fullscreen"></div>');
          }
        }
      });
      $(document).ajaxComplete(function (event, xhr, settings) {
        currentAJAXRequests = currentAJAXRequests.filter(function(ele){
          return ele !== settings.url;
        });
        if (removeProgressBar && currentAJAXRequests.length === 0) {
          removeProgressBar = false;
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

  // Add class to footer region when our brands block is present.
  Drupal.behaviors.ourBrandsBlock = {
    attach: function (context, settings) {

      /**
       * Place the Our brands block as per resolution.
       */
      function placeOurBrandsBlock() {
        // In mobile move the block after footer--menu.
        if ($(window).width() < 768) {
          $('footer .c-our-brands').insertAfter('.footer--menu');
        }
        // In desktop the block is above footer.
        if ($(window).width() > 1024) {
          $('footer .c-our-brands').insertBefore('.c-footer-primary');
        }
        // In tablet the correct position is inside the default footer region wrapper.
        if ($(window).width() > 767 && $(window).width() < 1025) {
          $('.region__footer-primary').append($('footer .c-our-brands'));
        }
      }

      // Check if our brands block is present in the footer to re-adjust the position.
      if ($('.c-our-brands').length) {
        placeOurBrandsBlock();
        // Limiting via debounce to 200ms.
        $(window).on('resize', debounce(function () {
          placeOurBrandsBlock();
        }, 200));
      }
    }
  };
})(jQuery, Drupal);
