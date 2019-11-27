/**
 * @file
 * Sub category scroll.
 */

(function ($, Drupal) {
  'use strict';

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
        // If target is above of the current view point in that case subcategory will be visible.
        if ($('.plp-subcategory-block').offset().top > $(element).offset().top) {
          stickyFilterPosition = stickyFilterPosition + $('.mobile-sticky-sub-category').outerHeight();
        }
        else {
          stickyFilterPosition = stickyFilterPosition - $('.mobile-sticky-sub-category').outerHeight();
        }
      }
    }
    else if ($(window).width() > 767 && $(window).width() < 1024) {
      // Height of sticky filter.
      stickyFilterPosition = 60;
      if ($('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
        if ($('.plp-subcategory-block').offset().top > $(element).offset().top) {
          stickyFilterPosition = stickyFilterPosition + $('.mobile-sticky-sub-category').outerHeight();
        }
      }
      else if (!$('body').hasClass('.header-sticky-filter') && !$('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
        stickyFilterPosition = $('.plp-subcategory-block').outerHeight() + stickyFilterPosition;
      }
    }
    else {
      if ($('.sticky-filter-wrapper').hasClass('show-sub-category') && $('.plp-subcategory-block').offset().top > $(element).offset().top) {
        stickyFilterPosition = $('.show-sub-category').outerHeight();
      }
      else if ($('.region__content').hasClass('filter-fixed-top') && $('.plp-subcategory-block').offset().top < $(element).offset().top) {
        stickyFilterPosition = $('.site-brand-home').outerHeight();
      }
      else {
        // Removing extra added 20px for top margin here when filters are not sticky.
        stickyFilterPosition = -20;
      }
    }

    // Adding 10px of margin from top so spacing doesn't look tight between term title and sticky facet filters.
    $('html, body').animate({
      scrollTop: ($(element).offset().top - stickyFilterPosition - 20)
    }, 500);
  }

  Drupal.behaviors.alshayaAcmProductCategorySubCategoryScroll = {
    attach: function () {
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
          // Hide sub category links when filtering and no data
          // available for a term.
          $(this).hide();
        }
        else {
          // Hide sub category links when removing filters and data
          // available now.
          $(this).show();
        }
      });
    }
  };
})(jQuery, Drupal);
