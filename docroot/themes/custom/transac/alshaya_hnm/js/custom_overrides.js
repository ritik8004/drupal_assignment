/**
 * @file
 * Custom JS for HM brand so we don't have to duplicate common JS from MC.
 */

(function ($, Drupal) {

  Drupal.behaviors.custom_overrides = {
    attach: function (context, settings) {

      // Overriding markup for the cart notification.
      Drupal.theme.cartNotificationMarkup = function (data) {
        var markup = '<div class ="notification">';
        markup += '<div class="col-1">';
        markup += '<img loading="lazy" src="' + data.image + '" alt="' + data.name + '" title="' + data.name + '">';
        markup += '</div>';
        markup += '<div class="col-2">';
        markup += '<span class="name">' + data.name + '</span>';
        markup += '<span class="qty-label">' + Drupal.t('Qty') + ':</span>';
        markup += '<span class="qty">' + data.quantity + '</span>';
        markup += '<span class="sub-text">' + Drupal.t('has been added to your cart.') + '</span>';
        markup += '<a href="' + data.link + '">' + data.link_text + '</a>';
        markup += '</div>';
        markup += '</div>';
        return markup;
      };

      $('.coupon-code-wrapper, .alias--cart #details-privilege-card-wrapper').each(function () {
        $(this).find('.details-privilege-card-wrapper-inside').css('height', 'auto');
      });

      $('.alias--user-register #details-privilege-card-wrapper').each(function () {
        $(this).find('.details-privilege-card-wrapper-inside').css('height', 'auto');
      });

      $('.path--user #details-privilege-card-wrapper').each(function () {
        $(this).find('.details-privilege-card-wrapper-inside').css('height', 'auto');
      });

      if (window.MobileDetect) {
        var md = new window.MobileDetect(window.navigator.userAgent);
        if (md.mobile()) {
          var $pdpBNPCtaDiv = $('.acq-content-product .basic-details-wrapper .mobile-only-show', context);
          $pdpBNPCtaDiv.toggle();
          var $pdpPostPayDiv = $('.acq-content-product .postpay.mobile-only-show', context);
          $pdpPostPayDiv.toggle();
          $('#pay-promo-mobile-comp', context).once('toggle-cta').click(function () {
            $pdpBNPCtaDiv.toggle();
            $pdpPostPayDiv.toggle();
            $('.pay-emi-lbl', this).toggleClass('ui-state-hide');
            $(this).toggleClass('lbl-open');
          });
        }
      }
    }
  };

})(jQuery, Drupal);
