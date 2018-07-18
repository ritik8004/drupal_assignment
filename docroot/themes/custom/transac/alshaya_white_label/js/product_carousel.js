/**
 * @file
 * Product featured carousel.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.productCategoryCarousel = {
    attach: function (context, settings) {
      var pdp_items_desk = drupalSettings.pdp_items_desk;
      var basket_carousel_items = drupalSettings.basket_carousel_items;
      var dp_product_carousel_items = drupalSettings.dp_product_carousel_items;
      var hp_product_carousel_items = drupalSettings.hp_product_carousel_items;

      var optionsBasket = {
        responsiveClass: true,
        dots: true,
        responsive: {
          1025: {
            items: basket_carousel_items,
            nav: true
          }
        }
      };

      var optionsPdp = {
        responsiveClass: true,
        dots: true,
        responsive: {
          1025: {
            items: pdp_items_desk,
            nav: true
          }
        }
      };

      var optionsPlp = {
        responsiveClass: true,
        dots: true,
        responsive: {
          1025: {
            items: dp_product_carousel_items,
            nav: true
          }
        }
      };

      var optionshp = {
        responsiveClass: true,
        dots: true,
        responsive: {
          1025: {
            items: hp_product_carousel_items,
            nav: true
          }
        }
      };

      function applyRtl(ocObject, options) {
        // For tablets and mobile we don't want to apply OwlCarousel.
        if ($(window).width() < 1024) {
          return;
        }
        // Get number of items.
        var itemsCount = ocObject.find('.views-row').length;

        // Check dynamically if looping is required and at which breakpoint.
        for (var i in options.responsive) {
          if (options.responsive[i]) {
            options.responsive[i].loop = (options.responsive[i].items < itemsCount);
          }
        }

        if (isRTL()) {
          ocObject.attr('dir', 'rtl');
          ocObject.owlCarousel(
            $.extend({}, options, {rtl: true})
          );
        }
        else {
          ocObject.owlCarousel(options);
        }

        // There is some issue with owl carousel, when we have more then one
        // carousel on the page it uses responsive settings from last one
        // always on page resize. We bind change event here and calculate loop
        // required or not dynamically every-time change is triggered after
        // page resize.
        ocObject.once('bind-event').on('change.owl.carousel', function (data) {
          try {
            if (data.property.name === 'settings') {
              var itemsCount = data.item.count;
              data.property.value.loop = (data.property.value.items < itemsCount);
            }
          }
          catch (e) {
            // We don't want anything to break because of this.
            // At max we will see duplicate items in carousel.
          }
        });
      }

      var plpfeaturedproduct = $('.nodetype--advanced_page .paragraph--type--product-carousel-category .product-category-carousel');
      var advancedfeaturedproduct = $('.frontpage .paragraph--type--product-carousel-category .product-category-carousel');
      var crossSell = $('.horizontal-crossell .owl-carousel');
      var upSell = $('.horizontal-upell .owl-carousel');
      var relatedSell = $('.horizontal-related .owl-carousel');
      var basketHR = $('.block-basket-horizontal-recommendation .owl-carousel');

      plpfeaturedproduct.each(function () {
        applyRtl($(this), optionsPlp);
      });

      advancedfeaturedproduct.each(function () {
        applyRtl($(this), optionshp);
      });

      basketHR.each(function () {
        applyRtl($(this), optionsBasket);
      });

      crossSell.each(function () {
        applyRtl($(this), optionsPdp);
      });

      upSell.each(function () {
        applyRtl($(this), optionsPdp);
      });

      relatedSell.each(function () {
        applyRtl($(this), optionsPdp);
      });
    }
  };
})(jQuery, Drupal);
