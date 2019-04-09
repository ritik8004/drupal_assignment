/**
 * @file
 * PLP Hover js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaFacetAllSlide = {
    attach: function (context, settings) {

      // Add active classes on facet dropdown content.
      $('.c-facet__title.c-accordion__title').once().on('click', function () {
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
        }
        else {
          $(this).parent().siblings('.c-facet').find('.c-facet__title.active').removeClass('active');
          $(this).addClass('active');
        }
      });

      var sortSelector = '.c-content__region .region__content .bef-exposed-form legend';
      $(sortSelector).once().on('click', function () {
        $(this).toggleClass('active');
      });

      // Close the sort and facets on click outside of them.
      document.addEventListener('click', function(event) {
        var sortBy = $('.c-content .c-content__region .bef-exposed-form').first();
        if ($(sortBy).find(event.target).length == 0) {
          $(sortBy).find('legend').removeClass('active');
        }

        var facet_block = $('.c-content .region__content > div.block-facets-ajax');
        if ($(facet_block).find(event.target).length == 0) {
          $(facet_block).find('.c-facet__title').removeClass('active');
        }
      });

      // Grid switch for PLP and Search pages.
      $('.small-col-grid').once().on('click', function () {
        $('.large-col-grid').removeClass('active');
        $(this).addClass('active');
        $('.c-products-list').removeClass('product-large').addClass('product-small');
      });
      $('.large-col-grid').once().on('click', function () {
        $('.small-col-grid').removeClass('active');
        $(this).addClass('active');
        $('.c-products-list').removeClass('product-small').addClass('product-large');
      });

      // On clicking facet block title, update the title of block and hide
      // other facets.
      $('.all-filters .block-facets-ajax').on('click', function() {
        // Update the title on click of facet.
        var facet_title = $(this).find('h3.c-facet__title').html();
        $('.filter-sort-title').html(facet_title);

        // Only show current facet and hide all others.
        $(this).removeClass('show-facet');
        $('.all-filters .block-facets-ajax').hide();
        $('.all-filters .bef-exposed-form').hide();
        $(this).addClass('show-facet');

        // Show the back button.
        $('.facet-all-back').show();
        // Update the the hidden field with the id of selected facet.
        $('#all-filter-active-facet-sort').val($(this).attr('id'));
      });

      // On clicking of sort option, update title
      $('.all-filters .bef-exposed-form').on('click', function() {
        // Update the title on click of facet.
        var facet_title = $(this).find('span.fieldset-legend').html();
        $('.filter-sort-title').html(facet_title);

        // Only show current facet and hide all others.
        $(this).removeClass('show-facet');
        $('.all-filters .block-facets-ajax').hide();
        $(this).addClass('show-facet');

        // Show the back button.
        $('.facet-all-back').show();
        // Update the the hidden field with the id of sort block.
        $('#all-filter-active-facet-sort').val($(this).attr('id'));
      });

      // On clicking on back button, reset the block title and add class so
      // that facet blocks can be closed.
      $('.facet-all-back').on('click', function() {
        $(this).hide();
        $('.filter-sort-title').html(Drupal.t('filter & sort'));
        $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').removeClass('show-facet');
        $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').show();
        $('.all-filters .bef-exposed-form legend').removeClass('active');
        // Reset the hidden field value.
        $('#all-filter-active-facet-sort').val('');
      });

      // Show all filters blocks.
      $('.show-all-filters').once().on('click', function() {
        $('.all-filters').addClass('filters-active');

        if ($(window).width() > 1023) {
          $('body').addClass('modal-overlay');
        }
        else {
          $('body').addClass('mobile--overlay')
        }

        $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').removeClass('show-facet');

        var active_filter_sort = $('#all-filter-active-facet-sort').val();
        // On clicking `all` filters, check if there was filter which selected last.
        if (active_filter_sort.length > 0) {
          $('.all-filters #' + active_filter_sort).show();
          $('.all-filters #' + active_filter_sort).addClass('show-facet');
        }
        $('.all-filters').show();
      });

      // Fake facet apply button to close the `all filter`.
      $('.facet-all-apply', context).once().on('click', function() {
        $('.all-filters').removeClass('filters-active');
        $('body').removeClass('mobile--overlay');
        $('body').removeClass('modal-overlay');
        $('.all-filters').hide();
        // Show filter count if applicable.
        showFilterCount();
      });

      if ($('.c-content__region .region__content  > div.block-facets-summary li.clear-all').length > 0) {
        var clearAll = $('.c-content__region .region__content  > div.block-facets-summary').clone();
        // Remove all `li` except last.
        $(clearAll).find('li:not(:last)').remove();
        $('.facet-all-clear').html(clearAll);
        $('.facet-all-clear').addClass('has-link');
      }
      else {
        $('.facet-all-clear').html(Drupal.t('clear all'));
        $('.facet-all-clear').removeClass('has-link');
      }

      // On change of outer `sort by`, update the 'all filter' sort by as well.
      $('.c-content .c-content__region .bef-exposed-form input:radio').on('click', function() {
        var idd = $(this).attr('id');
        $('.all-filters .bef-exposed-form input:radio').attr('checked', false);
        $('.all-filters .bef-exposed-form #' + idd).attr('checked', true);
      });

      // Sort result on change of sort in `All filters`.
      $('.all-filters .bef-exposed-form input:radio').on('click', function (e) {
        // Get ID.
        var idd = $(this).attr('id');
        $('.c-content .c-content__region .bef-exposed-form #' + idd).attr('checked', true);
        // Trigger click of button.
        var idd = $('.c-content .c-content__region [data-bef-auto-submit-click]').first().attr('id');
        $('#' + idd).trigger('click');
        // Stopping other propagation.
        e.preventDefault();
        e.stopPropagation();
      })

      showOnlyFewFacets();

      /**
       * Show filtercount on mobile on toggle buttons.
       */
      function showFilterCount() {
        // Only for mobile.
        if ($(window).width() < 768) {
          var filterBarSelector;
          if ($('body').hasClass('plp-page-only')) {
            filterBarSelector = '.block-facets-summary-blockfilter-bar-plp';
          }
          else if ($('body').hasClass('nodetype--acq_promotion')) {
            filterBarSelector = '.block-facets-summary-blockfilter-bar-promotions';
          }
          else {
            filterBarSelector = '.block-facets-summary-blockfilter-bar';
          }

          $(filterBarSelector +' ul li:not(.clear-all)').wrapAll('<div class="applied-filter"></div>');
          var height = $(filterBarSelector+ ' .applied-filter').height();
          // Add a max-height if there are filters on third line.
          if (height > 82) {
            $(filterBarSelector + ' .applied-filter').addClass('max-height');
          }

          // Count the number of filters on the third line onwards.
          var count = 0;
          $(filterBarSelector+ ' ul .applied-filter li').each(function () {
            if ($(this).position().top > 41) {
              count ++;
            }
          });
          if (count > 0) {
            $(filterBarSelector + ' .filter-toggle-mobile').show();
            $(filterBarSelector + ' .filter-toggle-mobile').html(count);
            $('.filter-toggle-mobile', context).once().on('click', function () {
              $(filterBarSelector + ' .applied-filter').toggleClass('max-height');
            });
          }
        }
      }

      /**
       * Show only 4 facets by default and hide others.
       */
      function showOnlyFewFacets() {
        var facets = $('.c-content__region .region__content > div.block-facets-ajax:not(:empty)');
        if (facets.length > 0) {
          // By default only show 4 facets.
          var show_only_facets = 4;
          var plugin_id = facets[0].getAttribute('data-block-plugin-id');
          // If block plugin id contains `category`, means its category facet.
          if (plugin_id.indexOf('category') !== -1) {
            // If category facet present. then index check increases.
            show_only_facets = 5;
          }
          facets.each( function (index) {
            if (index >= show_only_facets) {
              $(this).addClass('hide-facet-block');
            }
          });
        }
      }

      // Function to call in ajax command on facet refresh.
      // @see AlshayaSearchAjaxController::ajaxFacetBlockView()
      $.fn.refreshListGridClass = function () {
        if ($('.grid-buttons .large-col-grid').hasClass('active')) {
          $('.c-products-list').removeClass('product-small');
          $('.c-products-list').addClass('product-large');
        }
        else {
          $('.c-products-list').removeClass('product-large');
          $('.c-products-list').addClass('product-small');
        }

        var active_facet_sort = $('#all-filter-active-facet-sort').val();
        $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').removeClass('show-facet');
        if (active_facet_sort.length > 0) {
          $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').hide();
          $('.all-filters #' + active_facet_sort).addClass('show-facet');
          $('.all-filters #' + active_facet_sort).show();
        }

      };

    }
  };

})(jQuery, Drupal);
