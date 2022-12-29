/**
 * @file
 * Cart merge error handler.
 */

(function ($, Drupal) {
  Drupal.behaviors.alshayaXbMergeCartErrorhandler = {
    attach: function (context) {
      // Display error received from MDC on cart merge for XB.
      document.addEventListener('onCartMergeError',  (e) => {
        var data = e.detail.data;
        if (typeof data.error_message !== 'undefined') {
          var wrapper = $('[data-drupal-messages-fallback]');
          if (wrapper.length) {
            // Show error message from event data.
            wrapper.html('<div class="errors-container xb-errors"><div class="error">' + data.error_message + '</div></div>');
            wrapper.removeClass('hidden');
          }
        }
      });
    }
  };
})(jQuery, Drupal);
