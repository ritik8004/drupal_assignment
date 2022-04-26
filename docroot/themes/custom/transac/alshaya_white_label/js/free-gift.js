/**
 * @file
 * Free gift js file.
 */

/* global isRTL */

(function ($, Drupal) {

  function applyRtl(ocObject, options) {
    if (isRTL()) {
      ocObject.attr('dir', 'rtl');
      ocObject.once().slick(
        $.extend({}, options, {rtl: true})
      );
    }
    else {
      ocObject.once().slick(options);
    }
  }

  var optionFreeGifts = {
    arrows: true,
    useTransform: false,
    slidesToShow: 3,
    slidesToScroll: 1,
    focusOnSelect: false,
    touchThreshold: 1000,
    infinite: false
  };

  Drupal.behaviors.freeGiftsSlider = {
    attach: function (context, settings) {
      var shopByStory = $('.free-gifts-modal-overlay #drupal-modal .item-list ul');

      if ($(window).width() > 767) {
        setTimeout(function () {
          shopByStory.each(function () {
            applyRtl($(this), optionFreeGifts);
          });
        }, 5);
      }

      if ($('.free-gifts-modal-overlay').length > 0) {
        if ($('.free-gift-view').length > 0) {
          $('#drupal-modal').addClass('free-gift-listing-modal');
          $('#drupal-modal, body').removeClass('free-gift-detail-modal');
        }
        else {
          $('#drupal-modal, body').addClass('free-gift-detail-modal');
          $('#drupal-modal').removeClass('free-gift-listing-modal');
        }
      }

      $('.free-gift-promo-list a').once().on('click', function () {
        $('body').addClass('free-gift-promo-list-overlay');
      });

      $('.free-gift-title a, .free-gift-message a, .free-gift-image a, .gift-message a, .path--cart #table-cart-items table tr td.name a, .path--cart .cart-promotion-label-gift').on('click', function () {
        $('body').addClass('free-gifts-modal-overlay');
      });

      // On dialog close remove the free gift overlay related classes.
      $('.free-gifts-modal-overlay #drupal-modal').once().on('dialogclose', function () {
        if ($('body').hasClass('free-gift-promo-list-overlay')) {
          $('body').removeClass('free-gift-promo-list-overlay');
        }
        $('body').removeClass('free-gifts-modal-overlay');
      });

      $('#drupal-modal .short-description-wrapper').once('readmore').each(function () {
        $(this).on('click', '.read-more-description-link-gift', function () {
          $(this).parent().find('.desc-wrapper:first-child').hide();
          $(this).parent().find('.desc-wrapper:not(:first-child)').slideDown('slow');
          $(this).parent().scroll();
          $(this).replaceWith('<span class="show-less-link">' + Drupal.t('show less') + '</span>');
        });
        $(this).on('click', '.show-less-link', function () {
          $(this).parent().find('.desc-wrapper:not(:first-child)').slideUp('slow');
          $(this).parent().find('.desc-wrapper:first-child').slideDown('slow');
          $(this).replaceWith('<span class="read-more-description-link-gift">' + Drupal.t('Read more') + '</span>');
        });
      });

      $('.dialog-product-image-gallery-container button.ui-dialog-titlebar-close').on('mousedown', function () {
        var productGallery = $('#product-full-screen-gallery', $(this).closest('.dialog-product-image-gallery-container'));
        // Closing modal window before slick library gets removed.
        $(this).click();
        productGallery.slick('unslick');
        $('body').removeClass('pdp-modal-overlay');
      });

      // Only for cart page because on PDP we already get product_zoom.js.
      // So we will get that functionality there.
      if ($('.path--cart').length > 0 && $(window).width() < 768) {
        $(document).once('dialog-opened').on('click', '.dialog-product-image-gallery-container #product-full-screen-gallery img', function (e) {
          var productGallery = $('#product-full-screen-gallery', $(this).closest('.dialog-product-image-gallery-container'));
          // Closing modal window before slick library gets removed.
          $(this).closest('.dialog-product-image-gallery-container').find($('button.ui-dialog-titlebar-close')).trigger('mousedown');
          productGallery.slick('unslick');
          $('body').removeClass('pdp-modal-overlay');
          e.preventDefault();
        });
      }

      $('.sku-base-form').on('variant-selected', function (event, variant, code) {
        // We do not want to attach this event for variant change of PDP
        // product.
        if (!$(this).parents('#drupal-modal')) {
          return;
        }
        var sku = $(this).attr('data-sku');
        var selected = $('[name="selected_variant_sku"]', $(this)).val();
        var productKey = 'productInfo';
        var variantInfo = drupalSettings[productKey][sku]['variants'][variant];

        if (typeof variantInfo === 'undefined') {
          return;
        }

        var freeGiftWrapper = $(this).closest('article');
        if (selected === '' && drupalSettings.showImagesFromChildrenAfterAllOptionsSelected) {
          window.commerceBackend.updateGallery(freeGiftWrapper, drupalSettings[productKey][sku].layout, drupalSettings[productKey][sku].gallery, sku, variantInfo.sku);
        }
        else {
          window.commerceBackend.updateGallery(freeGiftWrapper, variantInfo.layout, variantInfo.gallery, sku, variantInfo.sku);
        }
      });
    }
  };

})(jQuery, Drupal);
