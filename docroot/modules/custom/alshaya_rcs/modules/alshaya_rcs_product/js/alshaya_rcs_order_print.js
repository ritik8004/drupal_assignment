(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaRcsOrderPrint = {
    attach: function (context, settings) {
        // @todo To figureout a way to generate PDF after images are present.
        setTimeout(function() {
          if ($("#rcs-ph-order_teaser").hasClass('rcs-loaded')) {
            window.print();
          }
        }, 2000);
    }
  }
})(jQuery, Drupal);
