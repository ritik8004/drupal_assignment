/**
 * @file
 * PLP Hover js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.facetPanel = {
    attach: function (context, settings) {

      // Add active classes on facet dropdown content.
      $('.c-facet__title.c-accordion__title').once().on('click', function () {
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          // We want to run this only on main page facets.
          if (!$(this).parent().parent().hasClass('filter__inner')) {
            $(this).siblings('ul').slideUp();
          }
        }
        else {
          if (!$(this).parent().parent().hasClass('filter__inner')) {
            $(this).parent().siblings('.c-facet').find('.c-facet__title.active').siblings('ul').slideUp();
          }
          $(this).parent().siblings('.c-facet').find('.c-facet__title.active').removeClass('active');
          // Check if sort by is open, else close it.
          if ($('.c-content .region__content > .views-exposed-form.bef-exposed-form').find('legend').hasClass('active')) {
            $(this).removeClass('active');
            $(this).siblings('.fieldset-wrapper').slideUp();
          }
          $(this).addClass('active');
          if (!$(this).parent().parent().hasClass('filter__inner')) {
            $(this).siblings('ul').slideDown();
          }
        }
      });

      var sortSelector = '.c-content__region .region__content .bef-exposed-form legend';
      $(sortSelector).once().on('click', function () {
        $(this).toggleClass('active');
        if ($(this).parents('.filter__inner').length === 0) {
          $(this).siblings('.fieldset-wrapper').slideToggle();
        }
      });

      // Close the sort and facets on click outside of them.
      document.addEventListener('click', function(event) {
        var sortBy = $('.c-content .region__content > .views-exposed-form.bef-exposed-form').first();
        if ($(sortBy).find(event.target).length == 0) {
          $(sortBy).find('legend').removeClass('active');
          $(sortBy).find('.fieldset-wrapper').slideUp();
        }

        var facet_block = $('.c-content .region__content > div.block-facets-ajax');
        if ($(facet_block).find(event.target).length == 0) {
          $(facet_block).find('.c-facet__title').removeClass('active');
          $(facet_block).find('ul').slideUp();
        }
      });

      // Grid switch for PLP and Search pages.
      $('.small-col-grid').once().on('click', function () {
        $('.large-col-grid').removeClass('active');
        $(this).addClass('active');
        $('.c-products-list').removeClass('product-large').addClass('product-small');
        setTimeout(function() {
          $('.search-lightSlider').slick('refresh');
         }, 300);
      });
      $('.large-col-grid').once().on('click', function () {
        $('.small-col-grid').removeClass('active');
        $(this).addClass('active');
        $('.c-products-list').removeClass('product-small').addClass('product-large');
        setTimeout(function() {
          $('.search-lightSlider').slick('refresh');
         }, 300);
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
        $('.all-filters .block-facets-ajax .c-facet__title').removeClass('active');
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
        updateSortTitle();
      });

      // Sort result on change of sort in `All filters`.
      $('.all-filters .bef-exposed-form input:radio').on('click', function (e) {
        // Get ID.
        var idd = $(this).attr('id');
        $('.c-content .c-content__region .bef-exposed-form #' + idd).attr('checked', true);
        // Trigger click of button.
        var idd = $('.c-content .c-content__region [data-bef-auto-submit-click]').first().attr('id');
        $('#' + idd).trigger('click');
        updateSortTitle();
        // Stopping other propagation.
        e.preventDefault();
        e.stopPropagation();
      })

      showOnlyFewFacets();
      updateSortTitle();
      updateFacetTitlesWithSelected();

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

        // If no category facet after ajax selection, add class to identify it.
        if ($('.all-filters #block-categoryfacetplp:not(:empty)').length === 0) {
          $('#block-alshaya-plp-facets-block-all').addClass('empty-category');
        }
        else {
          $('#block-alshaya-plp-facets-block-all').removeClass('empty-category');
        }

        // If there any active facet filter.
        updateFacetTitlesWithSelected();
      };

    }
  };

  /**
   * Update the facet titles with the selected value.
   */
  function updateFacetTitlesWithSelected() {
    // Iterate over each facet block.
    $('.all-filters .block-facets-ajax').each(function() {
      var facet_block = $(this);

      // Skip processing for price facets.
      if ($(facet_block).hasClass('price-facet-block')) {
        return;
      }

      var new_title = '';
      var total_selected = 0;
      var facets_to_show_in_label = 2;
      // If any facet item active.
      var active_facets = $(facet_block).find('ul li.is-active label span.facet-item__value');
      $.each(active_facets, function(index, element) {
        total_selected = total_selected + 1;
        // Show only two facets in title.
        if (total_selected <= facets_to_show_in_label) {
          var active_facet_label = $(element).contents().not($('span').children()).text().trim();
          new_title += active_facet_label + ', ';
        }
      })

      // Prepare the new title.
      var span_facet_title = '';
      var count_span = '';
      if (new_title.length > 0) {
        // Remove last `,` from the string.
        new_title = new_title.slice(0, -2);
        // Prepare the count span.
        if (total_selected > facets_to_show_in_label) {
          total_selected = total_selected - facets_to_show_in_label;
          count_span = '<span class="total-count"> (+' + total_selected + ')</span>';
        }
        span_facet_title = '<span class="selected-facets">' + new_title + count_span + '</span>';
      }

      // Append new title and count to facet title.
      var element_append = span_facet_title;
      $(facet_block).find('h3').find('.selected-facets').remove();
      $(facet_block).find('h3').find('.total-count').remove();
      $(facet_block).find('h3').append(element_append);
    });
  }

  // Update sort title on sort change/selection.
  function updateSortTitle() {
    // Get selected sort radio id.
    var selected_sort = $('.all-filters [data-drupal-selector="edit-sort-bef-combine"] [name="sort_bef_combine"]:checked').attr('id');
    // Get the label for the radio button.
    var for_label = $('.all-filters [data-drupal-selector="edit-sort-bef-combine"] label[for="' + selected_sort +'"]').text();
    var sort_label = '<span class="sort-for-label">' + for_label + '</span>';
    $('.all-filters [data-drupal-selector="edit-sort-bef-combine"] .fieldset-legend span.sort-for-label').remove();
    $('.all-filters [data-drupal-selector="edit-sort-bef-combine"] .fieldset-legend').append(sort_label);
  }

})(jQuery, Drupal);
