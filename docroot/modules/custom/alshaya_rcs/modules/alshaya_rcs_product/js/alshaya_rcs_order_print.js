(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaRcsOrderPrint = {
    attach: function (context, settings) {
      if ($("#rcs-ph-order_teaser").hasClass('rcs-loaded')) {
        setTimeout(function() {
          window.print();
        }, 1000);
      }
    }
  }
})(jQuery, Drupal);
