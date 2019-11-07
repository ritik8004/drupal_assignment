(function ($, Drupal, document) {
  'use strict';

  // Function to start fullpage loader wherever we need.
  function spinner_start() {
    if ($('.page-standard > .checkout-ajax-progress-throbber').length === 0) {
      $('.page-standard').append('<div class="ajax-progress ajax-progress-throbber checkout-ajax-progress-throbber"><div class="throbber"></div></div>');
    }
    $('.checkout-ajax-progress-throbber').show();
  }

  // Function to stop fullpage loader wherever we need.
  function spinner_stop() {
    $('.checkout-ajax-progress-throbber').hide();
  }

  Drupal.behaviors.alshayaAcmCartNotification = {
    attach: function (context, settings) {
      $('.sku-base-form').once('cart-notification').on('product-add-to-cart-success', function () {
        spinner_stop();

        if ($('.ui-dialog').length > 0) {
          $('.ui-dialog .ui-dialog-titlebar-close').trigger('click');
        }
      });

      $('.sku-base-form').once('cart-notification').on('product-add-to-cart-failed', function () {
        spinner_stop();
      });

      $(window).on('click', function () {
        if ($('#cart_notification').length) {
          // check if element is Visible
          var length = $('#cart_notification').html().length;
          if (length > 0) {
            $('#cart_notification').empty();
            $('body').removeClass('notification--on');
            $('#cart_notification').removeClass('has--notification');
          }
        }
      });

      // Stop event from inside container to propogate out.
      $('#cart_notification').once('bind-events').on('click', function (event) {
        event.stopPropagation();
      });

      $('.edit-add-to-cart', context).on('mousedown', function () {
        var that = this;
        // Delay loader for 10ms, we use the same event as AJAX and
        // we might have some client side validation error preventing
        // ajax call.
        setTimeout(function () {
          if ($(that).closest('form').hasClass('ajax-submit-prevented')) {
            // Scroll to error.
            scrollToErrorPDP();
            return;
          }

          // Start loading
          spinner_start();
        }, 10);
      });

      $('.edit-add-to-cart', context).on('keydown', function (event) {
        if ($(this).closest('form').hasClass('ajax-submit-prevented')) {
          // Scroll to error.
          scrollToErrorPDP();
          return;
        }

        if (event.keyCode === 13 || event.keyCode === 32) {
          // Start loading
          spinner_start();
        }
      });

      $(document).ajaxComplete(function (event, xhr, settings) {
        if ((settings.hasOwnProperty('extraData')) &&
          ((settings.extraData._triggering_element_name.indexOf('configurables') >= 0))) {
          spinner_stop();
        }
        else if (!settings.hasOwnProperty('extraData')) {
          spinner_stop();
        }
      });

      $.fn.cartNotificationScroll = function () {
        $('body').addClass('notification--on');
        $('#cart_notification').addClass('has--notification');
        // If magazine layout is enabled.
        if ($(window).width() < 768 && $('.magazine-layout').length > 0) {
          $('#cart_notification').addClass('cart-notification-animate');
          $('.promotions').find('.promotions-dynamic-label').trigger('cart:notification:animation:complete');
        }
        else {
          $('html, body').animate({
            scrollTop: $('.header--wrapper').offset().top
          }, 'slow');

          setTimeout(function () {
            $('#cart_notification').fadeOut();
            // Trigger a custom event cart:notification:animation:complete
            // Use this whenever we need to handle any JS animations after
            // cart notification animations are completed.
            $('body').removeClass('notification--on');
            $('.promotions').find('.promotions-dynamic-label').trigger('cart:notification:animation:complete');
          }, drupalSettings.addToCartNotificationTime * 1000);
        }
      };

      // On PDP scroll to first error label.
      var scrollToErrorPDP = function() {
        // Check pdp layout is default one or work for tab and above view port.
        if ($('.magazine-layout').length < 1 || $(window).width() > 767) {
          // Doing this for the JS conflict.
          setTimeout(function () {
            // First error label.
            var first_error_label = $('form.ajax-submit-prevented label.error').first();
            // If button is sticky (fix), just scroll.
            var is_button_sticky = $('button.edit-add-to-cart').hasClass('fix-button');

            // If error already visible, no need to scroll.
            if (isInViewPort(first_error_label) && !is_button_sticky) {
              return;
            }

            // Sticky header.
            var stickyHeaderHeight = stickyHeaderHight();
            // Scroll position.
            var height_to_scroll = first_error_label.offset().top - stickyHeaderHeight - 25;
            // Scroll to the error.
            $('html, body').animate({
              scrollTop: height_to_scroll
            });
          }, 500)
        }
      };

      // Calculate the sticky header hight.
      var stickyHeaderHight = function() {
        var brandingMenuHight = ($('.branding__menu').length > 0) ? $('.branding__menu').height() : 0;
        var superCategoryHight = ($('#block-supercategorymenu').length > 0) ? $('#block-supercategorymenu').height() : 0;

        // If mobile.
        if ($(window).width() < 768) {
          var mobileNavigationHight = ($('#block-mobilenavigation').length > 0) ? $('#block-mobilenavigation').height() : 0;
          return Math.max(brandingMenuHight, mobileNavigationHight) + superCategoryHight;
        }
        else {
          // Sticky header for desktop.
          return parseInt(brandingMenuHight) + parseInt(superCategoryHight);
        }

      };

      // Check if error element is visible.
      var isInViewPort = function(element) {
        var stickyHeader = stickyHeaderHight();
        var elementTop = element.offset().top;
        var elementBottom = elementTop + element.outerHeight();
        var viewportTop = $(window).scrollTop() + stickyHeader;
        var viewportBottom = viewportTop + $(window).height() + stickyHeader;
        return elementBottom  > viewportTop && elementTop < viewportBottom;
      };

      $.fn.cartGenericScroll = function (selector) {
        if ($(window).width() < 768 && $('body').find(selector).length !== 0) {
          $('html, body').animate({
            scrollTop: $(selector).offset().top - $('.branding__menu').height() - 100
          }, 'slow');
        }
      };
    }
  };

})(jQuery, Drupal, document);
