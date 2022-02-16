/**
 * @file
 * Alshaya Social auth popup.
 */

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.alshayaAppointmentSocial = {
    attach: function (context, settings) {
      // create new Social auth popup window and monitor it
      $(document).on('click', '.auth-link', function () {
        var authLink = $(this).attr('social-auth-link');
        Drupal.socialAuthPopup({
          path: authLink,
          callback: function () {
            window.location.reload();
          }
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
