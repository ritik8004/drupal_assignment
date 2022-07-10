<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

/**
 * Disable captcha during the behat test run.
 *
 * We will check if the `behat_secret_key` is set in the site settings and the
 * same is provided within the behat test URL as a `behat` query parameter. If
 * both are found and matched to each other, we will disable captcha for some
 * forms to allow executing behat test without manual interference.
 */
if (isset($settings['behat_secret_key'])
  // phpcs:ignore
  && isset($_REQUEST['behat'])
  // phpcs:ignore
  && $settings['behat_secret_key'] === $_REQUEST('behat')) {
  // Disable the captcha for the following form during the behat test runs.
  // User login form.
  $config['captcha.captcha_point.user_login_form']['status'] = FALSE;

  // User password reset form.
  $config['captcha.captcha_point.user_pass']['status'] = FALSE;

  // User registration form.
  $config['captcha.captcha_point.user_register_form']['status'] = FALSE;

  // Site wide contact form.
  $config['captcha.captcha_point.webform_submission_alshaya_contact_add_form']['status'] = FALSE;
}
