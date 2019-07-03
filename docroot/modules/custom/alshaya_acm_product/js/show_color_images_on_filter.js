/**
 * @file
 * JS file to show images from specific color on filter.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.skuShowColorImagesOnFilter = {
    attach: function (context, settings) {
      setTimeout(Drupal.skuShowColorImagesOnFilter, 1);

      // Ensure if results are populated first we show gallery properly.
      $('li.facet-item').once('show-gallery').on('click tap', function () {
        $(this).toggleClass('is-active');
        $(this).find('a').toggleClass('is-active');
      });

      $('[data-drupal-facets-summary-id] [data-drupal-facet-id]').once('show-gallery').on('click tap', function () {
        var id = $(this).attr('data-drupal-facets-summary-id');
        var value = $(this).attr('data-drupal-facet-item-value');
        var li = $('ul[data-drupal-facet-id="' + id + '"]').find('a[data-drupal-facet-item-value="' + value + '"]').parent('li');
        $(li).toggleClass('is-active');
        $(li).find('a').toggleClass('is-active');
      });
    }
  };

  Drupal.skuShowColorImagesOnFilter = function () {
    $('.list-product-gallery').once('show-gallery').each(function () {
      var first = $(this).find('span:first');
      var active = $('ul[data-drupal-facet-id*="' + first.attr('data--color-attribute') + '"]').find('a.is-active:first');

      if (active.length > 0) {
        $(this).find('span[data--color="' + active.attr('data-drupal-facet-item-value') + '"]').removeClass('hidden');
      }
      else {
        first.removeClass('hidden');
      }
    });
  }

})(jQuery, Drupal);
