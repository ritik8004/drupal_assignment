(function ($, Drupal, document) {

    Drupal.cartNotification = Drupal.cartNotification || {};

    Drupal.cartNotification.triggerNotification = function (data) {
      Drupal.cartNotification.spinner_stop();
      // Close the recommendation product modal if present.
      if ($('.ui-dialog').length > 0) {
        $('.ui-dialog .ui-dialog-titlebar-close').trigger('click');
      }

      var cart_notification_data = {};
      var show_crosssell = '';
      var showMatchbackNotification = drupalSettings.show_crosssell_as_matchback === true
        && drupalSettings.use_matchback_cart_notification === true;

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
        show_crosssell = showMatchbackNotification
          ? 'matchBackCartNotificationMarkup'
          : 'cartNotificationMarkup';
      }

      var matchback_class = showMatchbackNotification ? 'matchback-cart-notification' : '';

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
        var scroll = ($('.header-sticky-filter').length < 1 && $(window).width() > 767) ? true : false;
        $.fn.cartNotificationScroll(scroll);
        return;
      }

      $.fn.cartNotificationScroll(true);
    }

    Drupal.behaviors.alshayaAcmMiniCartNotification = {
      attach: function () {
        $(window).once('alshayaAcmMiniCartNotification').on('click', function () {
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
      }
    };

  })(jQuery, Drupal, document);
