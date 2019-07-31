/**
 * @file
 * Sub category scroll.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaAcmProductCategorySubCategoryScroll = {
    attach: function () {
      /**
       * Animate & Scroll to subcategory header.
       *
       * @param {*} tid
       */
      function scrollToCategoryHeader(tid) {
        var element = '#' + tid;
        var stickyFilterPosition;

        if ($(window).width() < 768) {
          stickyFilterPosition = $('#block-supercategorymenu').outerHeight() + $('#block-mobilenavigation').outerHeight() + $('.show-all-filters').outerHeight();
          if ($('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
            console.log(stickyFilterPosition);
            if ($('.plp-subcategory-block').offset().top > $(element).offset().top) {
              stickyFilterPosition = stickyFilterPosition + $('.mobile-sticky-sub-category').outerHeight();
            }
            else {
              stickyFilterPosition = stickyFilterPosition - $('.mobile-sticky-sub-category').outerHeight();
            }
          }
        }
        else if ($(window).width() > 767 && $(window).width() < 1024) {
          stickyFilterPosition = 60;
          if ($('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
            console.log(stickyFilterPosition);
            if ($('.plp-subcategory-block').offset().top > $(element).offset().top) {
              stickyFilterPosition = stickyFilterPosition + $('.mobile-sticky-sub-category').outerHeight();
            }
            else {
              stickyFilterPosition = stickyFilterPosition - $('.mobile-sticky-sub-category').outerHeight();
            }
          }
        }
        else {
          if ($('.sticky-filter-wrapper').hasClass('show-sub-category') && $('.plp-subcategory-block').offset().top > $(element).offset().top) {
            stickyFilterPosition = $('.show-sub-category').outerHeight();
          }
          else {
            stickyFilterPosition = $('#block-subcategoryblock + .block-views-exposed-filter-blockalshaya-product-list-block-1').outerHeight();
          }
        }

        $('html, body').animate({
          scrollTop: ($(element).offset().top - stickyFilterPosition)
        }, 500);
      }
      $('.sub-category').once().on('click', function() {
        var tid = $(this).attr('data-tid');
        setTimeout(function() {
          scrollToCategoryHeader(tid);
        }, 300);
      });

      if ($('#block-subcategoryblock').length > 0) {
        $('body').addClass('subcategory-listing-enabled');
      }

      // Hide the divs that do not have results.
      $('div.plp-subcategory-block').find('div.sub-category').each(function(){
        var tid = $(this).attr('data-tid');
        if($('term#' + tid).length === 0) {
          $(this).hide();
        }
      });
    }
  };
})(jQuery, Drupal);
