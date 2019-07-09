/**
 * @file
 * JS file to show images from specific color on filter.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.skuShowColorImagesOnFilter = {
    attach: function (context, settings) {
      setTimeout(Drupal.skuShowColorImagesOnFilter, 10);

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
      var url = $(this).attr('data--original-url');
      var selectedUrl = url + '?selected=';

      $(this).find('span[data--color]').addClass('hidden');
      if (active.length > 0) {
        var activeSpan = $(this).find('span[data--color="' + active.attr('data-drupal-facet-item-value') + '"]');
        activeSpan.removeClass('hidden');
        selectedUrl += activeSpan.attr('data--id');
      }
      else {
        first.removeClass('hidden');
        selectedUrl += first.attr('data--id');
      }

      $(this).parents('article').find('a[href]').attr('href', selectedUrl);
    });

    $('.search-lightSlider').slick('refresh');
  }

})(jQuery, Drupal);
