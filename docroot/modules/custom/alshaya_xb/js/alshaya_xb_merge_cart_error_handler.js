/**
 * @file
 * Cart merge error handler.
 */

(function ($, Drupal) {
  Drupal.behaviors.alshayaXbMergeCartErrorhandler = {
    attach: function (context) {
      // Display error received from MDC on cart merge for XB.
      document.addEventListener('onCartMergeError',  (e) => {
        var data = e.detail;
        if (typeof data.error_message !== 'undefined') {
          var wrapper = $('[data-drupal-messages-fallback]');
          // Show error message from event data.
          if (wrapper.length) {
            wrapper.html('<div class="errors-container"><div class="error">' + data.error_message + '</div></div>');
            wrapper.removeClass('hidden');
          }
        }
      });
    }
  };
})(jQuery, Drupal);
