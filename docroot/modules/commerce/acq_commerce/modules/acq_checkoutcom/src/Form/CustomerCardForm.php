<?php

namespace Drupal\acq_checkoutcom\Form;

use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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
   * CustomerCardForm constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   * @param \Drupal\acq_checkoutcom\CheckoutComAPIWrapper $checkout_com_Api
   *   Checkout.com api wrapper object.
   */
  public function __construct(
    ModuleHandlerInterface $module_handler,
    Request $current_request,
    CheckoutComAPIWrapper $checkout_com_Api
  ) {
    $this->moduleHandler = $module_handler;
    $this->currentRequest = $current_request;
    $this->checkoutComApi = $checkout_com_Api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('acq_checkoutcom.api')
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

    $form['payment_details']['checkout_kit'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => "
        window.CKOConfig = {
          debugMode: true,
          publicKey: 'pk_test_ed88f0cd-e9b1-41b7-887e-de794963921f',
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
        };",
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
        $this->t('Could not generate cko-card-token, there is something wrong.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $inputs = $form_state->getUserInput();

    $cardData = $this->checkoutComApi->storeNewCard(
      $this->currentRequest->get('user'),
      [
        'cardToken' => $inputs['cko-card-token'],
        'email' => $this->currentRequest->get('user')->getEmail(),
        'name' => $form_state->getValue('cc_name'),
      ]
    );

    echo '<pre>';
    print_r($cardData);
    echo '</pre>';

    $file = drupal_get_path('module', 'acq_checkoutcom') . '/saved_card_new.json';
    $data = file_get_contents($file);
    $data = array_merge(!empty($data) ? Json::decode($data) : [], [$cardData]);
    file_put_contents($file, Json::encode($data));
    die();
  }

}
