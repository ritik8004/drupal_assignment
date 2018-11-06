/**
 * @file
 * Overrides default facets/soft-limit.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.facetSoftLimit = {
    attach: function (context, settings) {
      if (typeof settings.facets !== 'undefined' && settings.facets.softLimit !== 'undefined') {
        $.each(settings.facets.softLimit, function (facet, limit) {
          Drupal.facets.applySoftLimit(facet, limit);
        });
      }

      // Do not apply soft limit if one of the options is selected.
      $('input.facets-checkbox:checkbox:checked').each(function () {
        if ($(this).closest('ul').nextAll('a').first().text() === Drupal.t('Show more')) {
          jQuery(this).closest('ul').nextAll('a').first().click();
        }
      });
    }
  };

  Drupal.facets = Drupal.facets || {};

  /**
   * Applies the soft limit UI feature to a specific facets list.
   *
   * @param {string} facet
   *   The facet id.
   * @param {string} limit
   *   The maximum amount of items to show.
   */
  Drupal.facets.applySoftLimit = function (facet, limit) {
    var zero_based_limit = limit - 1;
    var facetsList = $('ul[data-drupal-facet-id="' + facet + '"]');

    // Hide facets over the limit.
    facetsList.find('li:gt(' + zero_based_limit + ')').once('apply-soft-limit').hide();

    // Add "Show more" / "Show less" links.
    facetsList.once('apply-soft-limit').filter(function () {
      return $(this).find('li').length > limit;
    }).each(function () {
      $('<a href="#" class="facets-soft-limit-link"></a>').text(Drupal.t('Show more')).click(function () {
        // Override to handle multiple instances of same facet in DOM & show
        // more link moved out of facet list in DOM.
        var facet = $(this).siblings('ul.js-facets-checkbox-links, ul.item-list__swatch_list');
        if (facet.find('li:hidden').length > 0) {
          facet.find('li:gt(' + zero_based_limit + ')').slideDown(500);
          $(this).addClass('open').text(Drupal.t('Show less'));
        }
        else {
          facet.find('li:gt(' + zero_based_limit + ')').slideUp(500);
          $(this).removeClass('open').text(Drupal.t('Show more'));
        }
        return false;
      }).insertAfter($(this));
    });
  };

})(jQuery);
