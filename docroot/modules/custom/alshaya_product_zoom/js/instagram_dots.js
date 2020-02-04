/**
 * @file
 * Instagram style dots for Slick Slider on PDP Main Gallery - Mobile.
 */

/* global isRTL */

(function ($, Drupal) {
  'use strict';
  var transformValueBackward = 19;
  var transformValueForward = -19;
  // Distance in px - by how much you want to move the dots.
  // Negative for Forward/Next direction.
  var transformXIntervalNext = isRTL() ? transformValueBackward : transformValueForward;
  // Positive for Backward/Previous direction.
  var transformXIntervalPrev = isRTL() ? transformValueForward : transformValueBackward;
  var maxDots = 5;

  Drupal.behaviors.pdpInstagranDots = {
    attach: function (context, settings) {
      // Slick Selector.
      var slickSlider = $('#product-image-gallery-mobile, #product-image-gallery-mob, #product-full-screen-gallery', context);

      // After slider is loaded, add a wrapper for dots.
      // We need a wrapper with fixed width and overflow hidden.
      slickSlider.on('init', function (event, slick) {
        Drupal.behaviors.pdpInstagranDots.initialSetup($(this));
      });

      // Before change fires before any slides are changed.
      // So nextSlide is soon to be currentSlide.
      // currentSlide is the one which is visible when user scrolls.
      Drupal.behaviors.pdpInstagranDots.attachBeforeChange(slickSlider);
    }
  };

  /**
   * Add before change event.
   */
  Drupal.behaviors.pdpInstagranDots.attachBeforeChange = function (slickSlider) {
    slickSlider.once().on('beforeChange', function (event, slick, currentSlide, nextSlide) {
      // We do instagram dots only if we are above the limit.
      // Else, let the slider function as is, no changes.
      var totalCount = $(this).find('.slick-dots li').length;
      // Transform counter.
      var initialTranslate = $(this).find('ul.slick-dots .slick-dots-container').css('transform');
      var transformCount = Drupal.behaviors.pdpInstagranDots.getTranslateXFromMatrix(initialTranslate);
      if (totalCount > maxDots) {
        // The slider is moving forward.
        if (nextSlide > currentSlide) {
          // Check if the next slide is the n-pointer only then we add transform.
          if ($(this).find('ul.slick-dots li.dot-index-' + nextSlide).hasClass('n-small-1')) {
            // Check for last slide.
            if (!$(this).find('ul.slick-dots .slick-dots-container li:last-child').hasClass('n-small-1')) {
              // Add transform X to the list.
              transformCount = transformCount + transformXIntervalNext;
              $(this).find('ul.slick-dots li.dot-index-' + nextSlide).removeClass('n-small-1');
              var nextSlidePlusOne = nextSlide + 1;
              $(this).find('ul.slick-dots li.dot-index-' + nextSlidePlusOne).addClass('n-small-1');
              $(this).find('.slick-dots-container').css('transform', 'translateX(' + transformCount + 'px)');
              // Move the p-pointer forwards.
              var pPointer = nextSlide - 3;
              var pPointerMinusOne = pPointer - 1;
              $(this).find('ul.slick-dots .slick-dots-container li').eq(pPointerMinusOne).removeClass('p-small-1');
              $(this).find('ul.slick-dots .slick-dots-container li').eq(pPointer).addClass('p-small-1');
            }
          }
        }
        // The slider is moving backward.
        else {
          // Check if the next slide is the p-pointer only then we add transform.
          if ($(this).find('ul.slick-dots li.dot-index-' + nextSlide).hasClass('p-small-1')) {
            // Check for first slide.
            if (!$(this).find('ul.slick-dots .slick-dots-container li:first-child').hasClass('p-small-1')) {
              // Add transform X to the list.
              transformCount = transformCount + transformXIntervalPrev;
              $(this).find('ul.slick-dots li.dot-index-' + nextSlide).removeClass('p-small-1');
              var nextSlidePlusOne = nextSlide - 1;
              $(this).find('ul.slick-dots li.dot-index-' + nextSlidePlusOne).addClass('p-small-1');
              $(this).find('.slick-dots-container').css('transform', 'translateX(' + transformCount + 'px)');
              // Move the n-pointer backwards.
              var nPointer = currentSlide + 3;
              var nPointerMinusOne = nPointer - 1;
              $(this).find('ul.slick-dots .slick-dots-container li').eq(nPointer).removeClass('n-small-1');
              $(this).find('ul.slick-dots .slick-dots-container li').eq(nPointerMinusOne).addClass('n-small-1');
            }
          }
        }
      }
    });
  };

  /**
   * Add boundary pointers.
   *
   * @param slick
   * @param state
   */
  Drupal.behaviors.pdpInstagranDots.setBoundaries = function (slick, state) {
    // Set initial n pointer when slider is initialized.
    if (state === 'default') {
      // Add a pointer on last dot.
      slick.find('ul.slick-dots li').eq(4).addClass('n-small-1');
    }
  };

  /**
   * Setup work for dots.
   *
   * @param slick
   */
  Drupal.behaviors.pdpInstagranDots.initialSetup = function (slick) {
    // Add a container for transform and slide count.
    var dotsCount = slick.find('ul.slick-dots li').length;
    if (dotsCount < maxDots) {
      slick.find('ul.slick-dots').addClass('i-dots-inactive');
    }
    slick.find('ul.slick-dots').wrapInner('<div class="slick-dots-container"></div>');
    // Add a class to the dots so we don't mess other sliders.
    slick.find('ul.slick-dots').addClass('i-dots');
    // Add simple index on all dots.
    slick.find('ul.slick-dots .slick-dots-container li').each(function (index) {
      $(this).addClass('dot-index-' + index);
    });
    // Add default 0px transform to be manipulated later.
    slick.find('.slick-dots-container').css('transform', 'translateX(0)');
    // Initialize pointer.
    Drupal.behaviors.pdpInstagranDots.setBoundaries(slick,'default');
  };

  /**
   * Sync dots and slide for modal gallery.
   *
   * @param gallery
   * @param slideIndex
   * @param slideSync
   */
  Drupal.behaviors.pdpInstagranDots.syncDots = function (gallery, slideIndex, slideSync) {
    // If slideSync is true, scroll to the slide.
    if (slideSync === true) {
      // We add a delay for the modal to open and slick slider to render
      // to avoid race conditions.
      setTimeout(function () {
        gallery.slick('slickGoTo', parseInt(slideIndex), false);
      }, 500);
    }
    // Sync dots.
    var mainPDPGallery = $('#product-image-gallery-mobile');
    // If it is just a single image, then no need of sync.
    if (mainPDPGallery.find('.slick-slide').length === 1) {
      return;
    }
    var transformCount = mainPDPGallery.find('ul.slick-dots .slick-dots-container').css('transform');
    var translateX = Drupal.behaviors.pdpInstagranDots.getTranslateXFromMatrix(transformCount);
    var pPointerIndex = mainPDPGallery.find('ul.slick-dots li.p-small-1').index();
    var nPointerIndex = mainPDPGallery.find('ul.slick-dots li.n-small-1').index();
    // Apply transform.
    gallery.find('.slick-dots-container').css('transform', 'translateX(' + translateX + 'px)');
    // Reset old pointers.
    gallery.find('ul.slick-dots li').removeClass('n-small-1');
    gallery.find('ul.slick-dots li').removeClass('p-small-1');
    // Add new pointers.
    if (pPointerIndex >= 0) {
      gallery.find('ul.slick-dots li').eq(pPointerIndex).addClass('p-small-1');
    }
    gallery.find('ul.slick-dots li').eq(nPointerIndex).addClass('n-small-1');
  };

  Drupal.behaviors.pdpInstagranDots.getTranslateXFromMatrix = function (matrix) {
    // TranslateX is at 4.
    var split = matrix.split(',')[4];
    return parseInt(split);
  };
})(jQuery, Drupal);
