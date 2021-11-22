(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaRcsOrderPrint = {
    attach: function (context, settings) {
      if ($("#rcs-ph-order_teaser").hasClass('rcs-loaded')) {
        // @todo To figureout a way to generate PDF after images are present.
        setTimeout(function() {
          window.print();
        }, 5000);
      }
    }
  }
})(jQuery, Drupal);
