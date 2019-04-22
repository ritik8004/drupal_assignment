<?php

namespace Drupal\acq_checkoutcom\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CartConfigForm.
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
   * OrderSearchForm constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   */
  public function __construct(ModuleHandlerInterface $module_handler,
    Request $current_request) {
    $this->moduleHandler = $module_handler;
    $this->currentRequest = $current_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('request_stack')->getCurrentRequest()
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['payment_details'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['payment_details_checkout_com'],
      ],
      '#attached' => [
        'library' => ['acq_checkoutcom/checkoutcom.kit'],
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
          // Replace with api call.
          publicKey: 'pk_test_ed88f0cd-e9b1-41b7-887e-de794963921f',
          ready: function (event) {
            console.log('The Kit is ready');
            CheckoutKit.monitorForm('.acq-checkoutcom-customer-card-form', CheckoutKit.CardFormModes.CARD_TOKENISATION);
          },
          cardTokenised: function(event) {
            console.log(event);
            cardToken.value = event.data.cardToken
            document.getElementById('acq-checkoutcom-customer-card-form').submit();
          }
        };",
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 2,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $inputs = $form_state->getUserInput();

    // Authorise payment for the card.
    $output = acq_checkoutcom_custom_curl_request(
      "https://sandbox.checkout.com/api2/v2/charges/token",
      [
        'value' => (float) 1.0 * 100,
        'currency' => 'KWD',
        'cardToken' => $inputs['cko-card-token'],
        'autoCapture'   => 'N',
        'description'   => 'Saving new card',
        'email' => \Drupal::currentUser()->getEmail(),
      ]
    );

    echo '<pre>';
    print_r($output);
    echo '</pre>';

    // Validate authorisation.
    if( array_key_exists('status', $output) AND $output['status'] === 'Declined') {
      die('authorisation failed');
    }

    // Run the void transaction for the gateway.
    $void = acq_checkoutcom_custom_curl_request(
      "https://sandbox.checkout.com/api2/v2/charges/{$output['id']}/void",
      ['trackId' => '']
    );

    if( array_key_exists('errorCode', $void) ) {
      die('void failed');
    }

    // Prepare the card data to save
    $cardData = $output['card'];
    unset($cardData['customerId']);
    unset($cardData['billingDetails']);
    unset($cardData['bin']);
    unset($cardData['fingerprint']);
    unset($cardData['cvvCheck']);
    unset($cardData['name']);
    unset($cardData['avsCheck']);

    $file = drupal_get_path('module', 'acq_checkoutcom') . '/saved_card.json';
    $data = file_get_contents($file);
    $data = array_merge(!empty($data) ? json_decode($data) : [], [$cardData]);
    file_put_contents($file, json_encode($data));
    die();

    $url = "https://sandbox.checkout.com/api2/v2/customers/?email=" . urlencode(\Drupal::currentUser()->getEmail());
    $output = acq_checkoutcom_custom_curl_request($url);
    print_r($output);
    die();

    $output = acq_checkoutcom_custom_curl_request(
      "https://sandbox.checkout.com/api2/v2/customers",
      [
        'name' => 'Sarah Mitchell',
        'email' => \Drupal::currentUser()->getEmail(),
        'description' => 'checkout.com tokenisation poc from drupal',
        'cardToken' => $card_token,
      ]
    );

    print_r($output);
    die();
  }

}
