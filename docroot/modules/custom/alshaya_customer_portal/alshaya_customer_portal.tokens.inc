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
      switch ($name) {
        case 'iframe':
          $user = $data['user'] ?? NULL;
          if ($user instanceof UserInterface) {
            $config = \Drupal::config('alshaya_customer_portal.settings');
            $customer_portal_helper = \Drupal::service('alshaya_customer_portal.helper');
            // Check that user is not anonymous.
            if ($user->isAuthenticated()) {
              // Fetch user details to create SSO link.
              $bubbleable_metadata->addCacheableDependency($user);
              $bubbleable_metadata->addCacheableDependency($config);
              $bubbleable_metadata->addCacheContexts(['user']);
              $encrypted_string = $customer_portal_helper->getEncryptedDataForCustomerPortal($user);
              $sso_url = $config->get('logged_in_url') . $encrypted_string;
            }
            else {
              $sso_url = $config->get('anonymous_url');
            }
            $replacements[$original] = $customer_portal_helper->getIframeMarkup($sso_url, $user);
          }
          break;
      }
    }
  }

  return $replacements;
}
