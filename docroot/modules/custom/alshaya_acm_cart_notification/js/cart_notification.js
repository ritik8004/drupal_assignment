(function ($, Drupal, document) {
  'use strict';

  Drupal.cartNotification = Drupal.cartNotification || {};

  // Function to start fullpage loader wherever we need.
  Drupal.cartNotification.spinner_start = function () {
    if ($('.page-standard > .checkout-ajax-progress-throbber').length === 0) {
      $('.page-standard').append('<div class="ajax-progress ajax-progress-throbber checkout-ajax-progress-throbber"><div class="throbber"></div></div>');
    }
    $('.checkout-ajax-progress-throbber').show();
  }

  // Function to stop fullpage loader wherever we need.
  Drupal.cartNotification.spinner_stop = function () {
    $('.checkout-ajax-progress-throbber').hide();
  }

  // Get markup for the cart notification.
  Drupal.theme.cartNotificationMarkup = function (data) {
    var markup = '<div class="notification">';
    markup += '<div class="col-1">';
    markup += '<img src="' + data.image + '" alt="' + data.name + '" title="' + data.name + '">';
    markup += '<span class="qty">' + Drupal.t('Qty') + ': ' + data.quantity + '</span></div>';
    markup += '<div class="col-2"><span class="name">' + data.name + '</span>';
    markup += Drupal.t('has been added to your cart.');
    markup += '<a href="' + data.link + '">' + data.link_text + '</a>';
    markup += '</div>';
    markup += '</div>';
    return markup;
  };

  Drupal.theme.matchBackCartNotificationMarkup = function (data) {
    var markup = '<div class="matchback-notification notification">';
    markup += '<div class="matchback-cart-notification-close"></div>';
    markup += '<div class="col-1">';
    markup += '<img src="' + data.image + '" alt="' + data.name + '" title="' + data.name + '">';
    markup += '</div>';
    markup += '<div class="col-2">';
    markup += '<div class="name">' + data.name + '</div>';
    markup += '<div class="prod-added-text">' + Drupal.t('has been added to your cart.') + '</div>';
    markup += '<div classs="matchback-notification-qty">';
    markup += Drupal.t('Quantity: ');
    markup += '<span class="qty">' + data.quantity + '</span>';
    markup += '</div>';
    markup += '<div class="matchback-prod-added-text">' + Drupal.t('has been added to your cart.') + '</div>';
    markup += '<a href="' + data.link + '">' + data.link_text + '</a>';
    markup += '</div>';
    markup += '</div>';
    return markup;
  };

  Drupal.theme.cartNotificationTextMarkup = function (data) {
    var markup = '<div class="notification ' + data.class + '">';
    markup += data.text
    markup += '</div>';
    return markup;
  }

  Drupal.cartNotification.triggerNotification = function (data) {
    Drupal.cartNotification.spinner_stop();
    // Close the recommendation product modal if present.
    if ($('.ui-dialog').length > 0) {
      $('.ui-dialog .ui-dialog-titlebar-close').trigger('click');
    }

    var cart_notification_data = {};
    var show_crosssell = '';

    if ((typeof data.isTextNotification !== 'undefined') && data.isTextNotification) {
      cart_notification_data.text = data.text;
      cart_notification_data.class = (typeof data.type !== 'undefined') && (data.type === 'error')
        ? 'error-notification'
        : '';
      show_crosssell = 'cartNotificationTextMarkup';
    }
    else {
      cart_notification_data = {
        image: data.image,
        link: Drupal.url('cart'),
        link_text: Drupal.t('view cart'),
        name: data.product_name,
        quantity: data.quantity,
        class: '',
      };
      show_crosssell = drupalSettings.show_crosssell_as_matchback === true ? 'matchBackCartNotificationMarkup' : 'cartNotificationMarkup';
    }

    var matchback_class = drupalSettings.show_crosssell_as_matchback === true ? 'matchback-cart-notification' : '';

    // #cart_notification for the default mini cart icon.
    // #magv2_cart_notification for New PDP layout mobile cart icon.
    // #static_minicart_notification for StaticMinicart notification.
    $('#cart_notification, #magv2_cart_notification, #static_minicart_notification')
      .addClass(matchback_class)
      .addClass(cart_notification_data.class)
      .html(
        Drupal.theme(
          show_crosssell,
          cart_notification_data
        )
      );

    // We do not need a scroll if the noScroll option is set like when we add
    // to cart from listing pages.
    if (((typeof data.noScroll !== 'undefined') && data.noScroll === true)) {
      $.fn.cartNotificationScroll(false);
      return;
    }

    $.fn.cartNotificationScroll(true);
  }

  Drupal.behaviors.alshayaAcmCartNotification = {
    attach: function (context, settings) {
      $('.sku-base-form').once('cart-notification').on('product-add-to-cart-success', function (e) {
        var productData = e.detail.productData;
        Drupal.cartNotification.triggerNotification(productData);
      });

      $('.sku-base-form').once('cart-notification-error').on('product-add-to-cart-error', function () {
        Drupal.cartNotification.spinner_stop();
        scrollToErrorPDP();
      });

      $('.sku-base-form').once('cart-notification-failed').on('product-add-to-cart-failed', function () {
        Drupal.cartNotification.spinner_stop();
        scrollToErrorPDP();
      });

      $(window).on('click', function () {
        // #cart_notification for the default mini cart icon.
        // #magv2_cart_notification for New PDP layout mobile cart icon.
        // #static_minicart_notification for StaticMinicart notification.
        $('#cart_notification, #magv2_cart_notification, #static_minicart_notification').each(function () {
          if ($(this).html().length > 0) {
            $(this).empty();
            $('body').removeClass('notification--on');
            $(this).removeClass('has--notification');
          }
        });
      });

      // Stop event from inside container to propogate out.
      $('#cart_notification, #magv2_cart_notification, #static_minicart_notification').once('bind-events').on('click', function (event) {
        event.stopPropagation();
      });

      $('.edit-add-to-cart', context).on('mousedown', function () {
        var that = this;
        // Delay loader for 10ms, we use the same event as AJAX and
        // we might have some client side validation error preventing
        // ajax call.
        setTimeout(function () {
          // Start loading
          Drupal.cartNotification.spinner_start();
        }, 10);
      });

      $('.edit-add-to-cart', context).on('keydown', function (event) {
        if (event.keyCode === 13 || event.keyCode === 32) {
          // Start loading
          Drupal.cartNotification.spinner_start();
        }
      });

      $(document).ajaxComplete(function (event, xhr, settings) {
        if (!settings.hasOwnProperty('extraData')) {
          Drupal.cartNotification.spinner_stop();
        }
        else if ((settings.hasOwnProperty('extraData')) &&
          (settings.extraData._triggering_element_name !== undefined) &&
          ((settings.extraData._triggering_element_name.indexOf('configurables') >= 0))) {
          Drupal.cartNotification.spinner_stop();
        }
      });

      $.fn.cartNotificationScroll = function (scroll) {
        var scroll = (typeof scroll === 'undefined') ? true : scroll;

        // Fade In the the notification.
        $('#cart_notification, #magv2_cart_notification, #static_minicart_notification').fadeIn();

        // Add classes to indicate notification is present.
        $('body').addClass('notification--on');
        $('#cart_notification, #magv2_cart_notification, #static_minicart_notification').addClass('has--notification');

        // On Mobile - Check for Match back PDP notification.
        if ($(window).width() < 768 && $('.matchback-cart-notification').length > 0) {
          // Copy over the crossell products inside matchback.
          var element = $('.horizontal-crossell.mobile-only-block').clone();
          $('.matchback-cart-notification').append(element);
          $('body').addClass('matchback-notification-overlay');
          // Invoke blazy so that CS images can load.
          if (typeof Drupal.blazy !== 'undefined') {
            setTimeout(Drupal.blazy.revalidate, 500);
          }
          // Scroll to top for matchback notification.
          $('html, body').animate({
            scrollTop: 0
          }, 10);
          // Add event listener for match back close button.
          $('.matchback-cart-notification .matchback-cart-notification-close').on('mousedown', function () {
            $('#cart_notification, #magv2_cart_notification, #static_minicart_notification').removeClass('has--notification');
            $('body').removeClass('matchback-notification-overlay');
            $('.promotions').find('.promotions-dynamic-label').trigger('cart:notification:animation:complete');
          });
        }
        // On Mobile - If magazine layout is enabled.
        else if ($(window).width() < 768 && $('.magazine-layout').length > 0) {
          $('#cart_notification, #magv2_cart_notification, #static_minicart_notification').addClass('cart-notification-animate');
          $('.promotions').find('.promotions-dynamic-label').trigger('cart:notification:animation:complete');
        }
        // Above Mobile res / default PDP / new PDP.
        else {
          if (scroll) {
            $('html, body').animate({
              scrollTop: 0
            }, 'slow');
          }

          // Fadeout after few seconds.
          setTimeout(function () {
            $('#cart_notification, #magv2_cart_notification, #static_minicart_notification').fadeOut();

            // Trigger a custom event cart:notification:animation:complete
            // Use this whenever we need to handle any JS animations after
            // cart notification animations are completed.
            $('body').removeClass('notification--on');
            $('.promotions').find('.promotions-dynamic-label').trigger('cart:notification:animation:complete');
          }, drupalSettings.addToCartNotificationTime * 1000);
        }
      };

      // On PDP scroll to first error label.
      var scrollToErrorPDP = function () {
        // Check pdp layout is default one or work for tab and above view port.
        if ($('.magazine-layout').length < 1 || $(window).width() > 767) {
          // Doing this for the JS conflict.
          setTimeout(function () {
            // First error label.
            var first_error_label = $('.sku-base-form .error');
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
      var stickyHeaderHight = function () {
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
      var isInViewPort = function (element) {
        var stickyHeader = stickyHeaderHight();
        var elementTop = element.offset().top;
        var elementBottom = elementTop + element.outerHeight();
        var viewportTop = $(window).scrollTop() + stickyHeader;
        var viewportBottom = viewportTop + $(window).height() + stickyHeader;
        return elementBottom > viewportTop && elementTop < viewportBottom;
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
