<?php

/**
 * @file
 * Contains tokens provided by the module..
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\user\UserInterface;

/**
 * Implements hook_token_info().
 */
function alshaya_customer_portal_token_info() {
  $info = [];

  $info['types']['customer_portal'] = [
    'name' => t('Customer Portal'),
    'description' => t('Tokens for association with Customer Portal.'),
  ];

  $info['tokens']['customer_portal']['sso_url'] = [
    'name' => t('Iframe'),
    'description' => t('Iframe with embedded SSO to Customer Portal.'),
  ];

  $info['tokens']['customer_portal']['iframe'] = [
    'name' => t('Iframe'),
    'description' => t('Iframe with embedded SSO to Customer Portal.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_customer_portal_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'customer_portal') {
    foreach ($tokens as $name => $original) {
      if (in_array($name, ['iframe', 'sso_url'])) {
        $user = $data['user'] ?? NULL;
        if ($user instanceof UserInterface) {
          $config = \Drupal::config('alshaya_customer_portal.settings');
          $customer_portal_helper = \Drupal::service('alshaya_customer_portal.helper');
          // Check that user is not anonymous.
          if ($user->isAuthenticated()) {
            $encrypted_string = $customer_portal_helper->getEncryptedDataForCustomerPortal($user);
            $sso_url = $config->get('logged_in_url') . $encrypted_string;
            $bubbleable_metadata->addCacheableDependency($config);
            $bubbleable_metadata->addCacheContexts(['user']);
          }
          else {
            // Here we use logout url so that when a logged in user signs out
            // of Drupal and then visits the page with the iframe, then they
            // the iframe src will be set as logout url and the user will
            // get logged out of Customer portal. When user hits this URL
            // they will by default land on the homepage of Customer Portal.
            $sso_url = $config->get('logout_url');
          }

          if ($name === 'sso_url') {
            $replacements[$original] = $sso_url;
          }
          elseif ($name = 'iframe') {
            $replacements[$original] = $customer_portal_helper->getIframeMarkup($sso_url, $user);
          }
        }
      }
    }
  }

  return $replacements;
}
