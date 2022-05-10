(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaRcsOrderPrint = {
    attach: function (context, settings) {
        setTimeout(function() {
          if ($("#rcs-ph-order_teaser", context).once().hasClass('rcs-loaded')) {
            window.print();
          }
        }, 1);
    }
  }
})(jQuery, Drupal);
