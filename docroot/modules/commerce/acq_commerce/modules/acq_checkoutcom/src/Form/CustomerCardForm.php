<?php

namespace Drupal\acq_checkoutcom\Form;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomerCardForm.
 *
 * @package Drupal\acq_checkoutcom\Form
 */
class CustomerCardForm extends FormBase {

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Checkout.com api wrapper object.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComAPIWrapper
   */
  protected $checkoutComApi;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * CustomerCardForm constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   * @param \Drupal\acq_checkoutcom\CheckoutComAPIWrapper $checkout_com_Api
   *   Checkout.com api wrapper object.
   * @param \Drupal\acq_checkoutcom\ApiHelper $api_helper
   *   The api helper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    ModuleHandlerInterface $module_handler,
    Request $current_request,
    CheckoutComAPIWrapper $checkout_com_Api,
    ApiHelper $api_helper,
    ConfigFactoryInterface $config_factory,
    MessengerInterface $messenger
  ) {
    $this->moduleHandler = $module_handler;
    $this->currentRequest = $current_request;
    $this->checkoutComApi = $checkout_com_Api;
    $this->apiHelper = $api_helper;
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('acq_checkoutcom.checkout_api'),
      $container->get('acq_checkoutcom.agent_api'),
      $container->get('config.factory'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acq_checkoutcom_customer_card_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $user = NULL) {
    $form['uid'] = [
      '#type' => 'hidden',
      '#value' => $user->id(),
    ];

    $form['customer_id'] = [
      '#type' => 'hidden',
      '#value' => $user->get('acq_customer_id')->getString(),
    ];

    $form['payment_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_checkout_com'],
      ],
      '#attached' => [
        'library' => ['acq_checkoutcom/checkoutcom.kit'],
      ],
    ];

    $form['payment_details']['cc_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name on card'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-name', 'checkoutcom-input'],
        'data-customer-name' => 'card-sname',
      ],
    ];

    $form['payment_details']['cc_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Credit Card Number'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-input', 'checkoutcom-input'],
        'autocomplete' => 'cc-number',
        'data-checkout' => 'card-number',
      ],
    ];

    $form['payment_details']['cc_cvv'] = [
      '#type' => 'password',
      '#maxlength' => 4,
      '#title' => $this->t('Security code (CVV)'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-cvv-input', 'checkoutcom-input'],
        'autocomplete' => 'cc-csc',
        'data-checkout' => 'cvv',
      ],
    ];

    $form['payment_details']['cc_exp_month'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiration Month'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-exp-month-select', 'checkoutcom-input'],
        'data-checkout' => 'expiry-month',
      ],
    ];

    $form['payment_details']['cc_exp_year'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiration Year'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['checkoutcom-credit-card-exp-year-select', 'checkoutcom-input'],
        'data-checkout' => 'expiry-year',
      ],
    ];

    $form['payment_details']['card_token'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'cardToken',
      ],
    ];

    $debug = $this->configFactory->get('acq_checkoutcom.settings')->get('debug') ? 'true' : 'false';
    $script = "window.CKOConfig = {
      debugMode: {$debug},
      publicKey: '{$this->apiHelper->getSubscriptionKeys('public_key')}',
      ready: function (event) {
        CheckoutKit.monitorForm('.acq-checkoutcom-customer-card-form', CheckoutKit.CardFormModes.CARD_TOKENISATION);
      },
      cardTokenised: function(event) {
        cardToken.value = event.data.cardToken
        document.getElementById('acq-checkoutcom-customer-card-form').submit();
      },
      apiError: function (event) {
        // Remove any existing error messages.
        let list = document.getElementsByClassName('acq-checkoutcom-customer-card-form');
        if (list[0].firstElementChild.className == 'messages error') {
        let messageElement = document.getElementsByClassName('messages error');
        list[0].removeChild(messageElement[0]);
        }
        // Create error message.
        var errorMessage = document.createElement('div');
        errorMessage.setAttribute('class', 'messages error');
        errorMessage.innerHTML = event.data.errors.toString();
        list[0].prepend(errorMessage);
      },
    };";

    $form['payment_details']['checkout_kit'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => $script,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 2,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $inputs = $form_state->getUserInput();
    if (empty($inputs['cko-card-token'])) {
      $form_state->setError(
        $form,
        $this->t('Could not generate token, there is something wrong.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $inputs = $form_state->getUserInput();

    $card_data = $this->checkoutComApi->authorizeNewCard(
      $this->currentRequest->get('user'),
      [
        'cardToken' => $inputs['cko-card-token'],
        'email' => $this->currentRequest->get('user')->getEmail(),
        'name' => $form_state->getValue('cc_name'),
      ]
    );

    if (empty($card_data)) {
      $this->messenger->addError(
        $this->t('Something went wrong while saving your card, please contact administrator.')
      );
    }
    else {
      $user = $form_state->getBuildInfo()['args'][0];
      $this->apiHelper->storeCustomerCard($user, $card_data);
      Cache::invalidateTags(['user:' . $form_state->getValue('uid') . ':payment_cards']);

      $this->messenger->addStatus(
        $this->t('You card has been successfully saved.')
      );
    }

    $form_state->setRedirect('acq_checkoutcom.payment_cards', ['user' => $form_state->getValue('uid')]);
  }

}
