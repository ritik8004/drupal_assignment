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
      $('.block-views-exposed-filter-blockalshaya-product-list-block-1 legend').once().on('click', function () {
        $(this).toggleClass('active');
      });

      // Close the sort and facets on click outside of them.
      document.addEventListener('click', function(event) {
        var sortBy = $('.c-content .c-content__region .bef-exposed-form');
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

      // On clicking facet block title, update the title of block.
      var allFiltersFacets = '.all-filters .block-facets-ajax, .all-filters .block-views-exposed-filter-blockalshaya-product-list-block-1';
      $(allFiltersFacets).on('click', function() {
        // Update the title on click of facet.
        var facet_title = $(this).find('h3.c-facet__title').html();
        $('.filter-sort-title').html(facet_title);

        // Only show current facet and hide all others.
        $(allFiltersFacets).removeClass('show-facet');
        $('.all-filters .block-facets-ajax').hide();
        $('.all-filters .block-views-exposed-filter-blockalshaya-product-list-block-1:visible').hide();
        $(this).addClass('show-facet');

        // Show the back button.
        $('.facet-all-back').show();
      });

      // On clicking on back button, reset the block title and add class so
      // that facet blocks can be closed.
      $('.facet-all-back').on('click', function() {
        $(this).hide();
        $('.filter-sort-title').html(Drupal.t('Filter & Sort'));
        $(allFiltersFacets).removeClass('show-facet');
        $('.all-filters .block-views-exposed-filter-blockalshaya-product-list-block-1:hidden').show();
        $('.all-filters .block-facets-ajax').show();
      });

      // Show all filters blocks.
      $('.show-all-filters').once().on('click', function() {
        $('.all-filters').addClass('filters-active');
        $('body').addClass('modal-overlay');

        $('.all-filters').show();
      });

      // Fake facet apply button to close the `all filter`.
      $('.facet-all-apply').once().on('click', function() {
        $('.all-filters').removeClass('filters-active');
        $('body').removeClass('modal-overlay');

        $('.all-filters').hide();
      });

      if ($('.c-content__region .region__content  > div.block-facets-summary li.clear-all').length > 0) {
        var clearAll = $('.c-content__region .region__content  > div.block-facets-summary').clone();
        // Remove all `li` except last.
        $(clearAll).find('li:not(:last)').remove();
        $('.facet-all-clear').html(clearAll);
        $('.facet-all-clear').addClass('has-link');
      }
      else {
        $('.facet-all-clear').html(Drupal.t('Clear All'));
        $('.facet-all-clear').removeClass('has-link');
      }

      // On change of outer `sort by`, update the 'all filter' sort by as well.
      $('.c-content .c-content__region #edit-sort-bef-combine input:radio').on('click', function() {
        var idd = $(this).attr('id');
        $('.all-filters #edit-sort-bef-combine input:radio').attr('checked', false);
        $('.all-filters #edit-sort-bef-combine #' + idd).attr('checked', true);
      });

      // Sort result on change of sort in `All filters`.
      $('.all-filters #edit-sort-bef-combine input:radio').on('click', function (e) {
        // Get ID.
        var idd = $(this).attr('id');
        $('.c-content .c-content__region #edit-sort-bef-combine #' + idd).attr('checked', true);
        // Trigger click of button.
        var idd = $('.c-content .c-content__region [data-bef-auto-submit-click]').first().attr('id');
        $('#' + idd).trigger('click');
        // Stopping other propagation.
        e.preventDefault();
        e.stopPropagation();
      })

      showOnlyFewFacets();

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

    }
  };

})(jQuery, Drupal);
