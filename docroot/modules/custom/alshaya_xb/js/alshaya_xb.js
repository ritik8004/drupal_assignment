/**
 * @file
 * Error handler.
 */

(function ($, Drupal) {
  Drupal.behaviors.alshayaXbMergeCartErrorhandler = {
    attach: function (context) {
      document.addEventListener('mergeCartError',  (e) => {
        var data = e.detail;
        if (typeof data.error_message !== 'undefined') {
          $('.errors-container').html('<div class="error">' + data.error_message + '</div>');
        }
      });
    }
  };
})(jQuery, Drupal);
