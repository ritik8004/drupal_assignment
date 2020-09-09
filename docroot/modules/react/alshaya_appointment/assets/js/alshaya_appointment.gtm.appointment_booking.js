/**
 * @file
 * JS code to integrate with GTM for Appointment Booking.
 */

(function ($, Drupal, dataLayer, drupalSettings) {
  /**
   * Helper funciton to push Appointment Booking events.
   *
   * @param data
   */
  Drupal.alshaya_seo_gtm_push_appointment_booking = function (data) {
    dataLayer.push({
      event: 'appointment_booking',
      eventCategory: 'Appointment Booking',
      eventAction: data.eventAction,
      eventLabel: drupalSettings.user.uid > 0 ? 'Logged In' : 'Guest',
    });
  };

  document.addEventListener('appointmentBookingSteps', function (e) {
    Drupal.alshaya_seo_gtm_push_appointment_booking({
      'eventAction': e.detail.stepValue,
    });
  });
}(jQuery, Drupal, dataLayer, drupalSettings));
