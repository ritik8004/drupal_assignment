/**
 * @file
 * PLP Hover js file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaFacetAllSlide = {
    attach: function (context, settings) {
      $('.all-filters .block-facets-ajax').on('click', function() {
        // Update the title on click of facet.
        var facet_title = $(this).find('h3.c-facet__title').html();
        $('.filter-sort-title').html(facet_title);

        // Only show current facet and hide all others.
        $('.all-filters .block-facets-ajax').removeClass('show-facet');
        $('.all-filters .block-facets-ajax').addClass('hide-facet');
        $(this).removeClass('hide-facet');
        $(this).addClass('show-facet');

        $('.facet-all-back').show();
      });

      $('.facet-all-back').on('click', function() {
        $(this).hide();
        $('.filter-sort-title').html(Drupal.t('Filter & Sort'));
        $('.all-filters .block-facets-ajax').removeClass('show-facet');
        $('.all-filters .block-facets-ajax').addClass('hide-facet');

      });

      $('.show-all-filters').on('click', function() {
        $('.all-filters').show();
      });

      $('.facet-all-apply').on('click', function() {
        $('.all-filters').hide();
      });

      $('.facet-all-clear').on('click', function() {
        $('.clear-all a').trigger('click');
      });
    }
  };

})(jQuery, Drupal);
