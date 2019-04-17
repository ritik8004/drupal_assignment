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
        'id' => ['checkout_com_card_details'],
      ],
    ];

    $form['payment_details']['cc_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Credit Card Number'),
      '#default_value' => '',
      '#required' => TRUE,
    ];

    $form['payment_details']['cc_cvv'] = [
      '#type' => 'password',
      '#maxlength' => 4,
      '#title' => $this->t('Security code (CVV)'),
      '#default_value' => '',
      '#required' => TRUE,
    ];

    $form['payment_details']['cc_exp_month'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiration Month'),
      '#required' => TRUE,
    ];

    $form['payment_details']['cc_exp_year'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiration Year'),
      '#required' => TRUE,
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
