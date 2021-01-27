/**
 * @file
 * Sub category scroll JS file.
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
      stickyFilterPosition = $('#block-supercategorymenu').outerHeight() + $('#block-mobilenavigation').outerHeight() + $('.show-all-filters').outerHeight() + $('#block-subcategoryblock').outerHeight();
      if ($('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
        // If target is above of the current view point in that case subcategory will be visible.
        if ($('.plp-subcategory-block').offset().top > $(element).offset().top) {
          stickyFilterPosition = stickyFilterPosition + $('.mobile-sticky-sub-category').outerHeight() - 150;
        } else {
          stickyFilterPosition = stickyFilterPosition - $('.mobile-sticky-sub-category').outerHeight() - 20;
        }
      }
    } else if ($(window).width() > 767 && $(window).width() < 1024) {
      // Height of sticky filter.
      stickyFilterPosition = $('.container-without-product').outerHeight();
      if ($('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
        if ($('.plp-subcategory-block').offset().top > $(element).offset().top) {
          stickyFilterPosition = stickyFilterPosition + $('.mobile-sticky-sub-category').outerHeight() - 10;
        }
      } else if (!$('body').hasClass('header-sticky-filter') && !$('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
        // On page load when filters are not sticky and
        // adding 60 as fixed width for sticky filters which becomes sticky on scroll.
        stickyFilterPosition = -$('.show-all-filters').outerHeight() + 60;
      }
    } else {
      if ($('.sticky-filter-wrapper').hasClass('show-sub-category') && $('.plp-subcategory-block').offset().top > $(element).offset().top) {
        stickyFilterPosition = $('.show-sub-category').outerHeight();
      } else if ($('.region__content').hasClass('filter-fixed-top') && $('.plp-subcategory-block').offset().top < $(element).offset().top) {
        stickyFilterPosition = $('.site-brand-home').outerHeight() + 10;
      } else {
        // Adding 160px of margin from top so term title
        // doesn't hide behind sticky subcategory filters.
        stickyFilterPosition = $('.site-brand-home').outerHeight() + 160;
      }
    }

    // Adding 20px of margin from top so spacing doesn't look tight
    // between term title and sticky facet filters.
    $('html, body').animate({
      scrollTop: ($(element).offset().top - stickyFilterPosition - 20)
    }, 500);
  }

  Drupal.behaviors.subCategoryScroll = {
    attach: function () {
      $('.sub-category').once('category-scroll').on('click', function (e) {
        e.preventDefault();
        var tid = $(this).attr('data-tid');
        setTimeout(function () {
          scrollToCategoryHeader(tid);
        }, 150);
      });

      if ($('#block-subcategoryblock').length > 0) {
        $('body').addClass('subcategory-listing-enabled');
      }
    }
  };
})(jQuery, Drupal);
