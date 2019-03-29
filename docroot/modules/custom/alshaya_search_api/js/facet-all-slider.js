/**
 * @file
 * PLP Hover js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaFacetAllSlide = {
    attach: function (context, settings) {

      // On clicking facet block title, update the title of block.
      $('.all-filters .block-facets-ajax').on('click', function() {
        // Update the title on click of facet.
        var facet_title = $(this).find('h3.c-facet__title').html();
        $('.filter-sort-title').html(facet_title);

        // Only show current facet and hide all others.
        $('.all-filters .block-facets-ajax').removeClass('show-facet');
        $('.all-filters .block-facets-ajax').addClass('hide-facet');
        $(this).removeClass('hide-facet');
        $(this).addClass('show-facet');

        // Show the back button.
        $('.facet-all-back').show();
      });

      // On clicking on back button, reset the block title and add class so
      // that facet blocks can be closed.
      $('.facet-all-back').on('click', function() {
        $(this).hide();
        $('.filter-sort-title').html(Drupal.t('Filter & Sort'));
        $('.all-filters .block-facets-ajax').removeClass('show-facet');
        $('.all-filters .block-facets-ajax').addClass('hide-facet');

      });

      // Show all filters blocks.
      $('.show-all-filters').on('click', function() {
        $('.all-filters').show();
      });

      // Fake facet apply button to close the `all filter`.
      $('.facet-all-apply').on('click', function() {
        $('.all-filters').hide();
      });

      $('.three-col-grid').on('click', function() {
        $('.c-products-list').removeClass('product-small').addClass('product-large');
        $('.search-lightSlider').slick('refresh');
      });
      $('.four-col-grid').on('click', function() {
        $('.c-products-list').removeClass('product-large').addClass('product-small');
        $('.search-lightSlider').slick('refresh');
      });

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
              $(this).addClass('hidden-important');
            }
          });
        }
      }

    }
  };

})(jQuery, Drupal);
