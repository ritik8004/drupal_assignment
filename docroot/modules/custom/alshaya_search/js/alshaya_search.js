(function ($) {
  'use strict';
  Drupal.behaviors.alshayaSearch = {
    attach: function (context, settings) {
      $('#edit-sort-bef-combine option[value="search_api_relevance ASC"]').remove();
    }
  };

  Drupal.behaviors.alshayaFacets = {
    attach: function (context, settings) {
      $('.block-facet--checkbox').each(function() {
        // Prepend the text field before the checkboxes.
        $(this).find('ul').prepend('<input type="text" class="facets-search-input">').on('keyup', function () {
          var facetFilterKeyword = $(this).find('.facets-search-input').val();
          if (facetFilterKeyword) {
            // Hide show more if above keyword has some data.
            if (settings.facets.softLimit != undefined) {
              $(this).parent().find('.facets-soft-limit-link').hide();
            }
            $(this).find('li').each(function () {
              // Hide all facet links.
              $(this).hide();
              if ($(this).find('.facet-item__value').html().search(facetFilterKeyword) >= 0) {
                $(this).show();
              }
            });
          }
          else {
            // Show all facet items.
            $(this).find('li:hidden').show();
            if (settings.facets.softLimit != undefined) {
              // If soft limit is rendered, show the link.
              $(this).parent().find('.facets-soft-limit-link').show();
              if (!$(this).parent().find('.facets-soft-limit-link').hasClass('open')) {
                // Show only soft limit items, if facets were collapsed.
                var zero_based_limit = settings.facets.softLimit.color - 1;
                $(this).find('li:gt(' + zero_based_limit + ')').hide();
              }
            }
          }
        });
      });
    }
  };
})(jQuery);
