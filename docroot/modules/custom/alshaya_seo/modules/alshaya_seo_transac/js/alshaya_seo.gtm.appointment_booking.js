/**
 * @file
 * JS code to integrate with GTM for Appointment Booking.
 */

(function ($, Drupal, dataLayer, drupalSettings) {
  'use strict';

  /**
   * Helper funciton to push Appointment Booking events.
   *
   * @param eventAction
   */
  Drupal.alshaya_seo_gtm_push_appointment_booking = function (eventAction) {
    dataLayer.push({
      event: 'appointment_booking',
      eventCategory: 'Appointment Booking',
      eventAction: eventAction,
      eventLabel: drupalSettings.user.uid > 0 ? 'Logged In' : 'Guest',
    });
  };

  $(window).once('appointment-booking-load').on('load', function () {
    Drupal.alshaya_seo_gtm_push_appointment_booking($('.appointment-steps > li.active').attr('value'));
  });

  // Trigger GTM event on click of next step in appointment booking process.
  $(document).once('appointment-booking-next-step-clicked').on('click', '.appointment-flow-action button', function () {
    Drupal.alshaya_seo_gtm_push_appointment_booking($('.appointment-steps > li.active').next().attr('value'));
  });

  // Trigger GTM event on click of edit appointment steps.
  $(document).once('appointment-booking-edit-step-clicked').on('click', '.appointment-details-button.edit-button', function () {
    Drupal.alshaya_seo_gtm_push_appointment_booking($(this).attr('data-value'));
  });

  // Trigger GTM event on click of back on appointment steps.
  $(document).once('appointment-booking-back-step-clicked').on('click', '.appointment-type-button.back', function () {
    Drupal.alshaya_seo_gtm_push_appointment_booking($('.appointment-steps > li.active').prev().attr('value'));
  });

})(jQuery, Drupal, dataLayer, drupalSettings);