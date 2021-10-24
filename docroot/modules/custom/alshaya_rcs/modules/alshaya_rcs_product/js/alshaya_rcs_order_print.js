(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaRcsOrderPrint = {
    attach: function (context, settings) {
      if ($("#rcs-ph-order_teaser").hasClass('rcs-loaded')) {
        window.print();
      }
    }
  }
})(jQuery, Drupal);
