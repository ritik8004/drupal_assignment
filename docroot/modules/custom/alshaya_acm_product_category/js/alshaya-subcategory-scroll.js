/**
 * @file
 * Sub category scroll.
 */

(function ($, Drupal) {
  'use strict';

  // Global variable to set scroll position to be used in viewsScrollTop command.
  var exposedViewOffset;

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
        }
        else {
          stickyFilterPosition = stickyFilterPosition - $('.mobile-sticky-sub-category').outerHeight() - 20;
        }
      }
    }
    else if ($(window).width() > 767 && $(window).width() < 1024) {
      // Height of sticky filter.
      stickyFilterPosition = $('.container-without-product').outerHeight();
      if ($('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
        if ($('.plp-subcategory-block').offset().top > $(element).offset().top) {
          stickyFilterPosition = stickyFilterPosition + $('.mobile-sticky-sub-category').outerHeight() - 10;
        }
      }
      else if (!$('body').hasClass('header-sticky-filter') && !$('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
        // On page load when filters are not sticky and adding 60 as fixed width for sticky filters which becomes sticky on scroll.
        stickyFilterPosition = -$('.show-all-filters').outerHeight() + 60;
      }
    }
    else {
      if ($('.sticky-filter-wrapper').hasClass('show-sub-category') && $('.plp-subcategory-block').offset().top > $(element).offset().top) {
        stickyFilterPosition = $('.show-sub-category').outerHeight();
      }
      else if ($('.region__content').hasClass('filter-fixed-top') && $('.plp-subcategory-block').offset().top < $(element).offset().top) {
        stickyFilterPosition = $('.site-brand-home').outerHeight() + 10;
      }
      else {
        // Removing extra added 20px for top margin here when filters are not sticky.
        stickyFilterPosition = -20;
      }
    }

    // Adding 20px of margin from top so spacing doesn't look tight between term title and sticky facet filters.
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

  Drupal.behaviors.alshayaAcmProductCategorySubCategoryFilterSelectionScroll = {
    attach: function (context) {
      var plpBanner = $('.subcategory-listing-enabled .view-id-plp_promotional_banner', context);
      var superCategoryMenu = $('.subcategory-listing-enabled .block-alshaya-super-category-menu', context);
      var mobileNavigationMenu = $('.subcategory-listing-enabled .menu--mobile-navigation', context);
      $(window).once('html').on('load', function () {
        // To get the offset top of plp_subcategory_block, using banner offset top + banner height because
        // plp_subcategory_block’s offset top will keep changing.
        if($(window).width() < 768) {
          exposedViewOffset = plpBanner.height() + plpBanner.offset().top - superCategoryMenu.height() - mobileNavigationMenu.height();
        }
        else {
          exposedViewOffset = plpBanner.height() + plpBanner.offset().top;
        }
      });
    }
  };

  // Overriding Drupal core Views scroll to top ajax command specific to panty guide.
  Drupal.AjaxCommands.prototype.viewsScrollTop = function (ajax, response) {
    if (response){
      var offset = $(response.selector).offset();
      if (typeof offset !== 'undefined') {
        var scrollTarget = response.selector;
        while ($(scrollTarget).scrollTop() === 0 && $(scrollTarget).parent()) {
          scrollTarget = $(scrollTarget).parent();
        }
        if ((offset.top - 10 < $(scrollTarget).scrollTop())) {
          $('html, body').animate({
            scrollTop: exposedViewOffset
          }, 500);
        }
      }
    }
  };
})(jQuery, Drupal);
