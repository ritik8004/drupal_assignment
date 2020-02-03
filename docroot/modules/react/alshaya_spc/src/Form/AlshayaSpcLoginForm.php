<?php

namespace Drupal\alshaya_spc\Form;

use Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper;
use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaSpcLoginForm.
 *
 * @package Drupal\alshaya_spc\Form
 */
class AlshayaSpcLoginForm extends FormBase {

  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The login helper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper
   */
  protected $customerHelper;

  /**
   * AlshayaSpcLoginForm constructor.
   *
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   *   The email validator.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper $customer_helper
   *   The login helper.
   */
  public function __construct(
    EmailValidatorInterface $email_validator,
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaSpcCustomerHelper $customer_helper
  ) {
    $this->emailValidator = $email_validator;
    $this->entityTypeManager = $entity_type_manager;
    $this->customerHelper = $customer_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('email.validator'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_spc.customer_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ashaya_spc_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div class="checkout-login-separator order-3"><span>' . $this->t('or') . '</span></div>';
    $form['returning_customer'] = [
      '#markup' => '<span class="selected-tab-title mobile-only-block">' . $this->t('Sign In') . '</span>',
      '#weight' => -51,
    ];

    $config = $this->config('alshaya_acm_checkout.settings');

    if (!empty($config->get('checkout_guest_login.value'))) {
      $form['summary'] = [
        '#markup' => $config->get('checkout_guest_login.value'),
        '#weight' => -50,
      ];
    }

    $form['messages'] = [
      '#type' => 'status_messages',
      '#weight' => -49,
    ];

    // Display login form:
    $form['name'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#size' => 15,
      '#maxlength' => 256,
      '#required' => TRUE,
      '#attributes' => [
        'autocorrect' => 'none',
        'autocapitalize' => 'none',
        'spellcheck' => 'false',
      ],
      '#element_validate' => [
        'alshaya_valid_email_address',
      ],
    ];

    $form['pass'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#size' => 15,
      '#description' => $this->t('Enter the password that accompanies your username.'),
      '#required' => TRUE,
      '#attached' => [
        'library' => ['alshaya_white_label/unmask_password'],
      ],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('sign in'),
    ];

    $form['actions']['submit']['#attributes']['gtm-type'] = 'checkout-signin';

    // Get the forgot password link.
    $request_password_link = Url::fromRoute('user.pass', [], [
      'attributes' => [
        'title' => $this->t('Send password reset instructions via email.'),
        'class' => ['request-password-link'],
      ],
    ]);

    $form['request_password'] = Link::fromTextAndUrl($this->t('Forgot password?'), $request_password_link)->toRenderable();
    $form['request_password']['#weight'] = 101;

    $form['#attached']['library'][] = 'alshaya_user/email_validator_override';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if ($form_state->getErrors()) {
      return;
    }

    $values = $form_state->cleanValues()->getValues();

    // If not valid email address.
    if (!$this->emailValidator->isValid($values['name'])) {
      $this->messenger()->addError('Username does not contain a valid email.');
      $form_state->setErrorByName('custom', $this->t('Username does not contain a valid email.'));
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues()->getValues();
    $mail = $values['name'];
    $pass = $values['pass'];

    try {
      if ($uid = $this->customerHelper->authenticateCustomer($mail, $pass)) {
        $account = $this->entityTypeManager->getStorage('user')->load($uid);

        if ($account->isActive()) {
          $form_state->setRedirect('alshaya_spc.checkout');
          user_login_finalize($account);
        }
        else {
          $this->messenger()->addError($this->t('Your account has not been activated or is blocked.', [], ['context' => 'alshaya_static_text|account_already_exists']), 'error');
          $form_state->setErrorByName('custom', $this->t('Your account has not been activated or is blocked.', [], ['context' => 'alshaya_static_text|account_already_exists']));
        }
      }
      else {
        $this->messenger()->addError($this->t('Unrecognized email address or password.'));
        $form_state->setErrorByName('custom', $this->t('Unrecognized email address or password.'));
      }
    }
    catch (\Exception $e) {
      $form_state->setErrorByName('custom', $e->getMessage());
    }
  }

}
