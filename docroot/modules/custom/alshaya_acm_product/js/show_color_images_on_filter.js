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
    var firstGallery = $('.list-product-gallery span[data--color]:first');
    if (firstGallery.length === 0) {
      // Do nothing if no gallery to process.
      return;
    }

    // Get all active facet values for color attribute.
    var activeColors = $('ul[data-drupal-facet-id*="' + firstGallery.attr('data--color-attribute') + '"]').find('a.is-active');
    var activeFacetValues = [];
    $(activeColors).each(function () {
      activeFacetValues.push($(this).attr('data-drupal-facet-item-value'));
    });

    // We do it again if active facets change after ajax calls finish.
    // So adding length of active colors in .once().
    $('.list-product-gallery').once('show-gallery-' + activeColors.length).each(function () {
      var first = $(this).find('span[data--color]:first');
      var url = $(this).attr('data--original-url');
      var selectedUrl = url + '?selected=';
      var activeSpan = first;

      for (var i in activeFacetValues) {
        var activeColorGallery = $(this).find('span[data--color="' + activeFacetValues[i] + '"]:first');
        if (activeColorGallery.length > 0) {
          activeSpan = activeColorGallery;
          break;
        }
      }

      // It is possible we don't need to change and we are processing
      // second time, do not show blinking by hiding and showing gallery.
      if (activeSpan.hasClass('hidden')) {
        $(this).find('span[data--color]').addClass('hidden');
        activeSpan.removeClass('hidden');

        // Update all href to have selected param.
        selectedUrl += activeSpan.attr('data--id');
        $(this).parents('article').find('a.product-selected-url').attr('href', selectedUrl);
      }
    });

    // At the end, let's make sure sliders work fine.
    $('.search-lightSlider').slick('refresh');
  }

})(jQuery, Drupal);
