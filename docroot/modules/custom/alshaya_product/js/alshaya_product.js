(function ($) {
  'use strict';
  Drupal.behaviors.alshayaProduct = {
    attach: function (context, settings) {
      $('#edit-sort-bef-combine option[value="nid DESC"]').remove();
    }
  };
})(jQuery);
