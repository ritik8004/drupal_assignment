/**
 * @file
 * Search.
 */

(function ($, Drupal) {

  Drupal.behaviors.toggleSearch = {
    attach: function (context, settings) {
      $('.toggle-search').once().on('click', function () {
        $('.search-block').toggleClass('search-active');
      });
    }
  };

})(jQuery, Drupal);
