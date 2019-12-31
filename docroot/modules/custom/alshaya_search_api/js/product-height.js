/**
 * @file
 * Helper function to adjust height of product tiles for plp.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Helper function for Drupal.plpListingProductTileHeight() to find
   * tallest height in a row.
   */
  Drupal.findMaxRowHeight = function(indexStart, indexEnd, tiles) {
    var maxRowHeight = 0;
    // Find the tallest element in the row.
    for (var j = indexStart; j <= indexEnd; j++) {
      var elementHeight = $(tiles[j]).find('> article').outerHeight(true);
      if (elementHeight > maxRowHeight) {
        maxRowHeight = elementHeight;
      }
    }
    return maxRowHeight;
  };

  /**
   * Apply same height to all elements of a product listing row.
   */
  Drupal.plpRowHeightSync = function (indexStart, indexEnd, tiles) {
    var maxRowHeight = Drupal.findMaxRowHeight(indexStart, indexEnd, tiles);
    // Apply height to all tiles in row.
    var rowTiles = tiles.slice(indexStart, indexEnd+1);
    $.each(rowTiles, function(index, tile) {
      $(tile).css('height', maxRowHeight);
    });
  };

  /**
   * Calculate and add height for each product tile.
   *
   * @param mode
   * full_page mode or row mode.
   *
   * @param element
   * The img tag which is lazyloaded.
   */
  Drupal.plpListingProductTileHeight = function (mode, element) {
    var gridCount = 0;
    if ($(window).width() > 1024) {
      gridCount = $('.c-products-list').hasClass('product-large') ? 3 : 4;
    }
    else if ($(window).width() < 1024 && $(window).width() >= 768) {
      gridCount = $('.c-products-list').hasClass('product-large') ? 2 : 3;
    }
    else {
      gridCount = $('.c-products-list').hasClass('product-large') ? 1 : 2;
    }

    if ($('.subcategory-listing-enabled').length > 0) {
      Drupal.subCategoryListingPage(gridCount);
    }
    else {
      Drupal.plpListingPage(gridCount);
    }
  };

  Drupal.plpListingPage = function(gridCount) {
    var tiles = $('.c-products__item');
    var totalCount = $('.c-products__item').length;
    var loopCount = Math.ceil(totalCount / gridCount);
    var indexStart, indexEnd = 0;
    // In full page mode we dont factor lazy loading as this mode is to reorganize the tiles based on the grid.
    if (mode === 'full_page') {
      // Run for each row.
      for (var i = 0; i < loopCount; i++) {
        indexStart = gridCount * i;
        indexEnd = gridCount * i + gridCount - 1;
        Drupal.plpRowHeightSync(indexStart, indexEnd, tiles);
      }
    }

    else if (mode === 'row') {
      // Find the parent of the lazyloaded image, we dont want to take any action if the image is a swatch or
      // hover gallery image.
      if (!$(element).closest('*[data--color-attribute]').hasClass('hidden')
        && $(element).parents('.alshaya_search_slider').length <= 0
        && !$(element).hasClass('height-sync-processed')
      ) {
        $(element).addClass('height-sync-processed');
        var tile = $(element).parents('.c-products__item');
        var tileIndex = tiles.index(tile);
        // Find the row to iterate for height.
        var rowNumber = Math.ceil((tileIndex + 1) / gridCount);
        var rowIndex = rowNumber - 1;
        indexStart = gridCount * rowIndex;
        indexEnd = gridCount * rowIndex + gridCount - 1;
        Drupal.plpRowHeightSync(indexStart, indexEnd, tiles);
      }
    }
  };

  Drupal.subCategoryListingPage = function(gridCount) {
    var sections = [];
    $('.term-header').each(function() {
      sections.push($(this).get(0))
    });

    if (sections.length > 0) {
      for (var index in sections) {
        var currentTiles = [];
        var nextIndex = parseInt(index) + 1;
        if (typeof sections[nextIndex] == 'undefined') {
          currentTiles = $(sections[index]).nextAll();
        }
        else {
          currentTiles = $(sections[index]).nextUntil(sections[nextIndex]);
        }

        console.log(currentTiles);

        var totalCount = currentTiles.length;
        var loopCount = Math.ceil(totalCount / gridCount);
        var indexStart, indexEnd = 0;
        for (var i = 0; i < loopCount; i++) {
          indexStart = gridCount * i;
          indexEnd = gridCount * i + gridCount - 1;
          Drupal.plpRowHeightSync(indexStart, indexEnd, currentTiles);
        }
      }
    }
  };

}(jQuery, Drupal));
