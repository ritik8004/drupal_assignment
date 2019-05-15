/**
 * @file
 * Search and PLP DOM modifications and event handlers.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.alshayaPLPSearch = {
    attach: function (context, settings) {

      /**
       * Place the search count from view header in different locations based on resolution.
       */
      function placeSearchCount() {
        var viewHeader = null;
        var selector = null;
        if ($('body').hasClass('path--search')) {
          viewHeader = $('.c-plp .view-search .view-header');
          selector = $('.c-content__region .total-result-count, .facet-all-count');
        }
        else if ($('body').hasClass('nodetype--acq_promotion')) {
          viewHeader = $('.c-plp .view-alshaya-product-list .view-header');
          selector = $('.c-content__region .total-result-count, .facet-all-count');
        }
        else {
          viewHeader = $('.c-plp .view-alshaya-product-list .view-header');
          selector = $('.c-content__region .total-result-count, .facet-all-count');
        }
        viewHeader.addClass('search-count');
        var searchCount = $('.c-content__region .search-count');
        // For mobile.
        if ($(window).width() < 768) {
          $('.block-page-title-block').addClass('mobile');
          searchCount.removeClass('tablet');
          if (viewHeader.length) {
            searchCount.remove();
            selector.html(viewHeader);
          }
          searchCount.addClass('only-mobile');
        }
        // For tablet and desktop.
        else {
          $('.block-page-title-block').removeClass('mobile');
          searchCount.removeClass('only-mobile');
          if (viewHeader.length) {
            searchCount.remove();
            selector.html(viewHeader);
          }
          searchCount.addClass('tablet');
        }

        // For the count in the `All` category facet item.
        if ($('body #total_result_count').length === 0) {
          var total_count = $('.total-result-count .view-header').html();
          $('<input>').attr({
            type: 'hidden',
            id: 'total_result_count',
            value: total_count
          }).appendTo('body');
        }
        var count = $('#total_result_count').val().trim().split(' ')[0];
        $('li.category-all .facet-item__count').html('(' + count + ')');
      }

      // Clone the filter bar and add it to the filter menu on mobile.
      // Show mobile slider only on mobile resolution.
      placeSearchCount();
      $(window).on('resize', function (e) {
        placeSearchCount();
      });
    }
  };
})(jQuery);
