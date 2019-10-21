<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$env = 'local';

if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
}
elseif (getenv('TRAVIS')) {
  $env = 'travis';
}

// Set Book an appointment API url as per environment.
$settings['alshaya_ve_non_transac.settings']['book_appointment_url'] = 'https://staging-alshaya-cts.anzus.solutions/?customer=alshayave&wf=bookings&action=schedule&locationGroupId=visionexpress&appointmentTypeGroupId=visionexpress&questionId__channel=VisionExpress';
if ($env == '01live') {
  $settings['alshaya_ve_non_transac.settings']['book_appointment_url'] = 'https://alshaya-cts.anzus.solutions/?customer=alshayave&wf=bookings&action=schedule&locationGroupId=visionexpress&appointmentTypeGroupId=visionexpress&questionId__channel=VisionExpress';
}
