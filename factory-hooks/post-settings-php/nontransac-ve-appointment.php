<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Set Book an appointment API url as per environment.
// For non-prod env.
$settings['alshaya_ve_non_transac.settings']['book_appointment_url'] = 'https://staging-alshaya-cts.anzus.solutions/?customer=alshayave&wf=bookings&action=schedule&locationGroupId=visionexpress&appointmentTypeGroupId=visionexpress&questionId__channel=VisionExpress';

// For prod env.
if (isset($_ENV['AH_SITE_ENVIRONMENT']) && preg_match('/\d{2}(live|update)/', $_ENV['AH_SITE_ENVIRONMENT'])) {
  $settings['alshaya_ve_non_transac.settings']['book_appointment_url'] = 'https://alshaya-cts.anzus.solutions/?customer=alshayave&wf=bookings&action=schedule&locationGroupId=visionexpress&appointmentTypeGroupId=visionexpress&questionId__channel=VisionExpress';
}
