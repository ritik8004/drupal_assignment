/**
 * @file
 * Globaly required scripts to disable modal for mobile.
 */

(function ($, Drupal) {

  if ($(window).width() < 768) {
    if (navigator.userAgent.match(/Mobi/)) {
      $('a[data-dialog-type="modal"],  a.mobile-link').each(function () {
        $(this).removeClass('use-ajax');
        var href = $(this).attr('href');
        $(this).click(function (e) {
          e.preventDefault();
          window.location.href = href;
          return false;
        });
      });
    }
  }

  Drupal.behaviors.signupModal = {
    attach: function (context, settings) {
      function modalOverlay(button, className) {
        $(button).click(function () {
          $('body').removeClass(className);
        });
      }

      $('#block-alshaya-email-signup-link a').click(function () {
        $('body').addClass('signup-modal-overlay');
        modalOverlay('.ui-dialog-titlebar-close', 'signup-modal-overlay');

        $(document).ajaxComplete(function () {
          modalOverlay('.ui-dialog-titlebar-close', 'signup-modal-overlay');
        });
      });
    }
  };

})(jQuery, Drupal);
