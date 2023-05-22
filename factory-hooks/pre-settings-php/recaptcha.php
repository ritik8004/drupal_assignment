<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Recaptcha settings.
$settings['recaptcha.settings']['site_key'] = '6Le93BsUAAAAAMOiJ5wrk4ICF0N-dLs6iM_eR4di';
$settings['recaptcha.settings']['secret_key'] = '6Le93BsUAAAAABQ0RMy0TIFuKasg3uz8hqVl4c6n';

// Following works only for KW, SA and AE markets.
if (getenv('LANDO') || getenv('IS_DDEV_PROJECT')) {
  $config['recaptcha.settings']['site_key'] = '6LfTWGQfAAAAAF7zVwX6ieF1VdAIwPN2LfyfoU4i';
  $config['recaptcha.settings']['secret_key'] = '6LfTWGQfAAAAAJHkx4aYshAjLqM-CuETnlsjXvra';
}
