<?php

namespace Drupal\alshaya_user\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserDataInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides communication preference form.
 */
class UserCommunicationPreference extends FormBase {

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * The mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * The current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * UserCommunicationPreference constructor.
   *
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user data service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user service object.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   The MobileNumber util service object.
   */
  public function __construct(UserDataInterface $user_data, AccountInterface $account, MobileNumberUtilInterface $mobile_util) {
    $this->userData = $user_data;
    $this->account = $account;
    $this->mobileUtil = $mobile_util;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.data'),
      $container->get('current_user'),
      $container->get('mobile_number.util')
    );
  }

  /**
   * The current form id.
   *
   * @inheritdoc
   */
  public function getFormId() {
    return 'user_communication_preference';
  }

  /**
   * Return the form element array.
   *
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $user = NULL) {
    $this->user_profile = $account = $user;

    $account = User::load($this->user_profile->id());

    if (!alshaya_acm_customer_is_customer($account)) {
      throw new AccessDeniedHttpException();
    }

    $form_state->set('user', $account);

    // Display email as communication preference.
    $options = [
      'email' => $this->t('Email') . ' <span>(' . $account->getEmail() . ')</span>',
    ];

    // Display mobile as communication preference if not empty.
    if (!empty($account->field_mobile_number->getValue())) {
      $options['mobile'] = $this->t('Mobile') . ' <span>(' . $this->mobileUtil->getFormattedMobileNumber($account->field_mobile_number->getValue()[0]['value']) . ')</span>';
    }

    $preference = $this->userData->get('user', $this->user_profile->id(), 'communication_preference');

    $form['communication_preference'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select your preferred communication channel'),
      '#options' => $options,
      '#default_value' => $preference ?: ['email'],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Save')];

    return $form;
  }

  /**
   * Submit handler for the form.
   *
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $preference = $form_state->getValue('communication_preference');
    $this->userData->set('user', $this->user_profile->id(), 'communication_preference', $preference);
    drupal_set_message($this->t('Your communication preference saved successfully.'));
  }

}
