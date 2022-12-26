<?php

namespace Drupal\alshaya_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\alshaya_spc\Helper\SecureText;
use Drupal\Core\Site\Settings;

/**
 * Customer controller to add/override pages for customer.
 */
class UserController extends ControllerBase {

  /**
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * UserController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   */
  public function __construct(Request $current_request) {
    $this->currentRequest = $current_request;
  }

  /**
   * Returns the build to registration completed page.
   *
   * @return array
   *   Build array.
   */
  public function registerComplete() {
    // Get data from query string.
    $encryptedUserId = $this->currentRequest->query->get('user');

    // Redirect to home if no value in query string.
    if (empty($encryptedUserId)) {
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // Decode that data from query string.
    $userId = SecureText::decrypt(
        $encryptedUserId,
        Settings::get('hash_salt')
      );

    // Redirect to home if value in query string is invalid.
    if (empty($userId)) {
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // Load the user.
    $account = $this->entityTypeManager()->getStorage('user')->load($userId);

    // Check if no user found or user is already active.
    if (empty($account) || $account->isActive()) {
      return new RedirectResponse(Url::fromRoute('<front>')->toString());
    }

    // Clear the status messages set by other modules.
    $this->messenger()->messagesByType('status');

    $build = [];

    // Get text from config.
    $text = $this->config('alshaya_user.settings')->get('user_register_complete.value');

    // Use email from session and replace in text.
    $build['#markup'] = str_replace('[email]', $account->getEmail(), $text);

    $build['#cache'] = ['max-age' => 0];

    $gender_selection = '';
    if ($account->hasField('field_gender')) {
      $allowed_values = [
        'm' => 'Male',
        'f' => 'Female',
        'ns' => 'Prefer not to say',
      ];
      $gender = $account->get('field_gender')->value;
      $gender_selection = $allowed_values[$gender] ?? '';
    }

    $email_preference = $account->hasField('field_subscribe_newsletter') ?
      $account->get('field_subscribe_newsletter')->value : '';

    $build['#attached']['drupalSettings']['alshaya_gtm_create_user_gender'] = $gender_selection;
    $build['#attached']['drupalSettings']['alshaya_gtm_create_user_newsletter'] = $email_preference == 1 ? 'Yes' : 'No';

    if ($account->get('field_subscribe_newsletter')->getString()) {
      $build['#attached']['drupalSettings']['alshaya_gtm_create_user_lead'] = $account->id();
      $build['#attached']['drupalSettings']['alshaya_gtm_create_user_pagename'] = 'registration';
      if (stripos($this->currentRequest->server->get('HTTP_REFERER'), '/cart/checkout/confirmation') !== FALSE) {
        $build['#attached']['drupalSettings']['alshaya_gtm_create_user_pagename'] = 'confirmation';
      }
    }

    if ($account->get('field_privilege_card_number')->getString()) {
      $build['#attached']['drupalSettings']['alshaya_gtm_create_user_pc'] = $account->get('field_privilege_card_number')->getString();
    }

    return $build;
  }

  /**
   * Page callback for setting message after logout in change password.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to login page.
   */
  public function passwordChangedLogout() {
    // Set the message now.
    $this->messenger()->addMessage($this->t('Your password has been changed.'));
    return $this->redirect('user.login');
  }

}
