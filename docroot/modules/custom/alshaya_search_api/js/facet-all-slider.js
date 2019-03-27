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
        $(this).addClass('show-facet');
      });

      $('.show-all-filters').on('click', function() {
        $('.all-filters').show();
      });

      $('.facet-all-apply').on('click', function() {
        $('.all-filters').hide();
      });
    }
  };

})(jQuery, Drupal);
