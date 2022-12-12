/**
 * @file
 * Promo panel js.
 */

(function ($, Drupal) {

  Drupal.behaviors.promoPanel = {
    attach: function (context, settings) {
      var $offer_toggler = $('.block-promo-panel-wrapper .promo-panel-label', context);
      var $mobile_link = $('.block-promo-panel-wrapper .promo-panel-label a', context);
      var $body = $('body');

      // Tracking promotion block in body footer. (E.g. Today's Offers block in VS)
      $('.block-promo-panel-wrapper h3.promo-panel-label, .block-promo-panel-wrapper .slick-slider button.slick-arrow').once('js-event').on('click', function () {
        // Clicked on block label.
        if ($(this).find('a.mobile-link').length > 0) {
          // Do not trigger if promo block is already displayed.
          if ($(this).closest('.block-promo-panel-wrapper').hasClass('active-promo-panel')) {
            return;
          }
          var promoElements = $(this).siblings('div.slick-slider').find('div.slick-track');
          var promoBlockLabel = $(this).find('a.mobile-link').attr('gtm-title').toUpperCase();
        }
        // Clicked on arrow elements.
        else {
          var promoElements = $(this).siblings('div.slick-list').find('div.slick-track');
          var promoBlockLabel = $(this).closest('.block-promo-panel-wrapper').find('a.mobile-link').attr('gtm-title').toUpperCase();
        }
        // First three active items.
        var frontPromoElements = $(promoElements).find('div.slick-active');
        if (frontPromoElements) {
          // Ignore repeating items from slider behaviour.
          // Settings from alshaya_white_label/slider.
          var extraElements = ($(window).width() < 1025) ? 4 : 6;
          var promotionItemCount = $(promoElements).find('div.slick-slide').length - extraElements;
          // In case slider does not appear when only 2/3 items.
          promotionItemCount = (promotionItemCount < 0) ? $(promoElements).find('div.slick-slide').length : promotionItemCount;
          var promoBlockDetails = {
            promotionItemCount: promotionItemCount,
            promoBlockLabel: promoBlockLabel,
          };
          // Push to GTM.
          Drupal.alshaya_seo_gtm_push_promotion_impressions(frontPromoElements, 'footer promo panel', 'promotionImpression', promoBlockDetails);
        }
      });

      // Tracking promotion item click in body footer.
      // (E.g. Today's Offers block item in VS).
      $('.slick-slide .field--name-field-banner, .slick-slide .field--name-field-link a').once('js-event').on('click', function () {
        var clickedPromoElements = $(this).closest('div.slick-slide');
        // Ignore repeating items from slider behaviour.
        // Settings from alshaya_white_label/slider.
        var extraElements = ($(window).width() < 1025) ? 4 : 6;
        // Plus 1 taken as siblings() does not count the self element.
        var promotionItemCount = clickedPromoElements.siblings().length - extraElements + 1;
        // In case slider does not appear when only 2/3 items.
        promotionItemCount = (promotionItemCount < 0) ? clickedPromoElements.siblings().length + 1 : promotionItemCount;
        var promoBlockDetails = {
          promotionItemCount: promotionItemCount,
          promoBlockLabel: $(this).closest('.block-promo-panel-wrapper').find('a.mobile-link').attr('gtm-title').toUpperCase(),
        };
        // Push to GTM.
        Drupal.alshaya_seo_gtm_push_promotion_impressions(clickedPromoElements, 'footer promo panel', 'promotionClick', promoBlockDetails);
      });

      $($offer_toggler).once('alshaya_promo_panel').on('click', function () {
        $(window).trigger('resize');
        $(this).parent().toggleClass('active-promo-panel');
        $($body).toggleClass('active-promo-panel-content');
      });

      $($mobile_link).once('alshaya_promo_mobile_link').on('click', function (e) {
        if(/Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
          return true;
        }
        else {
          e.preventDefault();
        }
      });

      /*
       * Promo panel sticky on scroll.
       */
      function checkOffset() {
        if ($('.c-footer').length > 0) {
          if ($('.block-promo-panel-wrapper .promo-panel-label').offset().top >= $('.c-footer').offset().top - 49) {
            $('.block-promo-panel-wrapper .promo-panel-label').addClass('label-not-fixed'); // restore on scroll down
            $('.block-promo-panel-wrapper').removeClass('promo-panel-fixed').addClass('promo-static'); // restore on scroll down
          }

          if ($(document).scrollTop() + window.innerHeight < $('.c-footer').offset().top) {
            $('.block-promo-panel-wrapper .promo-panel-label').removeClass('label-not-fixed');
            $('.block-promo-panel-wrapper').addClass('promo-panel-fixed').removeClass('promo-static');
          }
        }
      }

      checkOffset();

      $(window, context).scroll(function () {
        checkOffset();
      });

      // Close Promo panel when clicked anywhere outside.
      var $promo_panel_content = $('.promo__panel > .field--name-field-paragraph-content', context);

      $('body', context).on('click', function (e) {
        if (!$(e.target).closest($promo_panel_content).length && !$(e.target).closest($offer_toggler).length) {
          $($offer_toggler).parent().removeClass('active-promo-panel');
          $($body).removeClass('active-promo-panel-content');
        }
      });
    }
  };

})(jQuery, Drupal);
