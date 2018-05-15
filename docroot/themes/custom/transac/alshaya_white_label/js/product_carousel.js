/**
 * @file
 * product featured carousel.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.productCategoryCarousel = {
    attach: function (context, settings) {

      var optionsBasket = {
        responsiveClass: true,
        dots: false,
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
            items: 5,
            nav: true
          }
        }
      };

      var optionsPdp = {
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
            items: 2,
            nav: true
          },
          1025: {
            items: 3,
            nav: true
          }
        }
      };

      var optionsPlp = {
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

      // This and next function is added to swipe two items together in mobile.
      function onDrag(event) {
        this.initialCurrent = event.relatedTarget.current();
      }

      // This and prev function is added to swipe two items together in mobile.
      function onDragged(event) {
        var owl = event.relatedTarget;
        var draggedCurrent = owl.current();

        if (draggedCurrent > this.initialCurrent) {
          owl.current(this.initialCurrent);
          // @TODO: Make speed dynamic based on config.
          owl.next(750);
          owl.next(500);
        }
        else if (draggedCurrent < this.initialCurrent) {
          owl.current(this.initialCurrent);
          // @TODO: Make speed dynamic based on config.
          owl.prev(750);
          owl.prev(500);
        }
      }

      function applyRtl(ocObject, options) {
        // Get number of items.
        var itemsCount = ocObject.find('.views-row').length;

        // Check dynamically if looping is required and at which breakpoint.
        for (var i in options.responsive) {
          if (options.responsive[i]) {
            options.responsive[i].loop = (options.responsive[i].items < itemsCount);
          }
        }

        var transient = {};

        // Slide by 2 by default.
        options.onDrag = onDrag.bind(transient);
        options.onDragged = onDragged.bind(transient);

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
