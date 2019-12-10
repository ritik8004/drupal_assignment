/**
 * @file
 * Helper function to adjust height of product tiles for plp.
 */

/**
 * Helper function for Drupal.plpListingProductTileHeight() to find
 * tallest height in a row.
 */
function findMaxRowHeight(indexStart, indexEnd, tiles) {
  var maxRowHeight = 0;
  // Find the tallest element in the row.
  for (var j = indexStart; j <= indexEnd; j++) {
    var elementHeight = $(tiles[j]).find('> article').outerHeight(true);
    if (elementHeight > maxRowHeight) {
      maxRowHeight = elementHeight;
    }
  }
  return maxRowHeight;
}

/**
 * Apply same height to all elements of a product listing row.
 */
function plpRowHeightSync (indexStart, indexEnd, tiles) {
  var maxRowHeight = findMaxRowHeight(indexStart, indexEnd, tiles);
  // Apply height to all tiles in row.
  var rowTiles = tiles.slice(indexStart, indexEnd+1);
  $.each(rowTiles, function(index, tile) {
    $(tile).css('height', maxRowHeight);
  });
}