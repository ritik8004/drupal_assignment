(function ($) {
  'use strict';
  Drupal.behaviors.alshayaSearch = {
    attach: function (context, settings) {
      $('#edit-sort-bef-combine option[value="search_api_relevance ASC"]').remove();
    }
  };
})(jQuery);
