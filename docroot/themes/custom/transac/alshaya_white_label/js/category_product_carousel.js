/**
 * @file
 * product featured carousel.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.productCategoryCarousel = {
    attach: function (context, settings) {

      var plpfeaturedproduct = $('.nodetype--advanced_page .paragraph--type--product-carousel-category .product-category-carousel');
      var advancedfeaturedproduct = $('.frontpage .paragraph--type--product-carousel-category .product-category-carousel');

      var options = {
        responsiveClass: true,
        dots: true,
        responsive: {
          0: {
            items: 2,
            nav: true,
            stagePadding: 25,
            mouseDrag: true
          },
          768: {
            items: 4,
            nav: true
          },
          1025: {
            items: 5,
            nav: true
          }
        }
      };

      var optionshp = {
        responsiveClass: true,
        dots: true,
        responsive: {
          0: {
            items: 2,
            nav: false,
            stagePadding: 25,
            mouseDrag: true
          },
          768: {
            items: 4,
            nav: true
          },
          1025: {
            items: 6,
            nav: true
          }
        }
      };

      // This function is duplicated as of now in sell.js
      // Check if we can merge it.
      function applyRtl(ocObject, options) {
        // Get number of items.
        var itemsCount = ocObject.find('.views-row').length;

        // Check dynamically if looping is required and at which breakpoint.
        for (var i in options.responsive) {
          options.responsive[i].loop = (options.responsive[i].items < itemsCount);
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

      plpfeaturedproduct.each(function () {
        applyRtl($(this), options);
      });

      advancedfeaturedproduct.each(function () {
        applyRtl($(this), optionshp);
      });
    }
  };
})(jQuery, Drupal);
