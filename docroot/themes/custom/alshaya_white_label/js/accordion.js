/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {
      function moveContextualLink(parent, body) {
        if (typeof body === 'undefined') {
          body = '.c-accordion__title';
        }
        $(parent).each(function () {
          var contextualLink = $(this).find(body).next();
          $(this).append(contextualLink);
        });
      }

      /**
       * Toggles the footer accordions.
       */

      if ($('.c-footer-is-accordion').length) {
        var accordionHead = $('.c-footer-is-accordion .is-accordion');
        var accordionBody = $(accordionHead).nextAll();

        $(accordionBody).addClass('accordion--body');
        $(accordionHead).click(function () {
          var $ub = $(this).nextAll().stop(true, true).slideToggle();
          accordionBody.not($ub).slideUp();
          $ub.parent().toggleClass('open--accordion');
          accordionBody.not($ub).parent().removeClass('open--accordion');
        });
      }

      if (context === document) {
        moveContextualLink('.c-accordion');

        $('.c-facet__blocks').accordion({
          header: '.c-accordion__title'
        });

        if ($('.c-facet__blocks__wrapper').length) {
          var facetBlockWrapper = $('.c-facet__blocks__wrapper').clone(true, true);
          var mainBlock = $('.block-system-main-block');
          var facetLabel = facetBlockWrapper.find('.c-facet__label');
          var facetBlock = facetBlockWrapper.find('.c-facet__blocks');

          facetBlockWrapper.addClass('c-facet__blocks__wrapper--mobile').addClass('is-filter');
          if ($('body').hasClass('path--search')) {
            mainBlock.before(facetBlockWrapper);
            var searchFilter = $('.c-search #views-exposed-form-search-page');
            searchFilter.wrapAll('<div class="view-filters is-filter">');
            $('.is-filter').wrapAll('<div class="filter--mobile clearfix">');
            $('.block-views-exposed-filter-blocksearch-page .c-facet__blocks__wrapper').insertBefore('.view-filters.is-filter');
          }
          else {
            mainBlock.after(facetBlockWrapper);
            var viewFilter = $('.c-products-list .view-filters');
            viewFilter.addClass('is-filter');
            $('.is-filter').wrapAll('<div class="filter--mobile clearfix">');
          }
          facetLabel.click(function () {
            $('.page-wrapper, .header--wrapper, .c-pre-content').toggleClass('show-overlay');
            facetLabel.toggleClass('is-active');
            facetBlock.toggle();
          });
        }

        $('.c-search .view-search .view-header').addClass('search-count');
        $('.c-search .view-search .view-header').insertBefore('#block-filterbar');

        // Hiding the filter border if there are no filters.
        var checkFilter = $.trim($('.c-search .region__content .block-facets-summary-blockfilter-bar').html());
        console.log(checkFilter.length);
        if (checkFilter.length) {
          $('.c-search .region__content .block-facets-summary-blockfilter-bar').css('border-bottom-width', '1px');
        }
        else {
          $('.c-search .region__content .block-facets-summary-blockfilter-bar').css('border-bottom-width', '0');
        }
      }

      /**
      * Toggles the Expand Order Accordions.
      */

      if ($('.recent__orders--list .order-summary-row').length) {
        var parentOrder = $('.recent__orders--list .order-summary-row');
        var listOrder = $('.recent__orders--list .order-item-row');

        $(listOrder).hide();

        $(parentOrder).click(function () {
          var $ub = $(this).nextAll().stop(true, true).slideToggle();
          listOrder.not($ub).hide();
          $ub.parent().toggleClass('open--accordion');
          listOrder.not($ub).parent().removeClass('open--accordion');
        });
      }
    }
  };

})(jQuery, Drupal);
