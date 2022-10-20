/**
 * @file
 * Utility function for the rcs listing.
 */

(function (Drupal, drupalSettings) {
  /**
   * Utility function to get the top level category from the URL.
   */
  Drupal.getTopLevelCategoryUrl = function () {
    // Get the first category item from the path.
    var rcsFullPath = drupalSettings.rcsPage.fullPath;

    if (rcsFullPath) {
      rcsFullPath = rcsFullPath.split('/');

      return rcsFullPath[1];
    }

    return '';
  };
})(Drupal, drupalSettings);
