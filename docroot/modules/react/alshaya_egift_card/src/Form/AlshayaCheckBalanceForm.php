<?php

namespace Drupal\alshaya_egift_card\Form;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check balance modal form class.
 */
class AlshayaCheckBalanceForm extends FormBase{

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaCheckBalanceForm constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper, FormBuilder $formBuilder, ConfigFactoryInterface $config_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->formBuilder = $formBuilder;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_api.api'),
      $container->get('form_builder'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'check_balance_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['#prefix'] = '<div id="check-balance-form">';
    $form['#suffix'] = '</div>';

    $form['help'] = [
      '#type' => 'item',
      '#markup' => $this->t('Enter gift card details to check balance and validity.', [], ['context' => 'egift']),
    ];

    $form['card_number'] = [
      '#type' => 'textfield',
      '#attributes' => ['placeholder' => $this->t('eGift Card Number', [], ['context' => 'egift'])],
      '#required' => FALSE,
      '#maxlength' => 16,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#required' => FALSE,
      '#attributes' => [
        'placeholder' => $this->t('Email address', [], ['context' => 'egift']),
        'autocorrect' => 'none',
        'autocapitalize' => 'none',
        'spellcheck' => 'false',
      ],
      '#element_validate' => [
        'alshaya_valid_email_address',
      ],
    ];

    $form['api_error'] = [
      '#type' => 'item',
      '#markup' => '<div id="api-error"></div>',
    ];

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('CHECK BALANCE', [], ['context' => 'egift']),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $card_number = $form_state->getValue('card_number');
    $email = $form_state->getValue('email');
    $ajax_response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $ajax_response->addCommand(new ReplaceCommand('#check-balance-form', $form));
    }
    else {
      $endpoint = 'egiftcard/getBalance';
      $data["accountInfo"] = [
        "cardNumber" => $card_number,
        "email" => $email
      ];
      $response = $this->apiWrapper->invokeApi($endpoint, $data, 'JSON');
      $response = is_string($response) ? Json::decode($response) : $response;
      if (empty($response)) {
        $ajax_response->addCommand(new HtmlCommand('#api-error', $this->t('Something went wrong, please try again later.', [], ['context' => 'egift'])));
        $ajax_response->addCommand(new HtmlCommand('.form-item--error-message', ''));
      }
      else {
        $currency_code = $this->configFactory->get('acq_commerce.currency')->get('currency_code');
        $replace_modal_form = $this->formBuilder->getForm('Drupal\alshaya_egift_card\Form\AlshayaBalanceReplaceForm');
        $ajax_response->addCommand(new OpenModalDialogCommand($this->t('Check Balance & Validity', [], ['context' => 'egift']), $replace_modal_form, ['width' => 'auto', 'height' => 'auto']));
        if (!empty($response['card_number'])) {
          $ajax_response->addCommand(new HtmlCommand('#card-number', substr($response['card_number'], -4)));
        }else{
          $ajax_response->addCommand(new HtmlCommand('#card-details', $response['card_number']));
        }
        if (!empty($response['current_balance'])) {
          $ajax_response->addCommand(new HtmlCommand('#balance', $currency_code . ' ' . $response['current_balance']));
        }
        if (!empty($response['validity'])) {
          $ajax_response->addCommand(new HtmlCommand('#validity', $response['validity']));
        }else {
          $ajax_response->addCommand(new HtmlCommand('#validity-details', ''));
        }
      }
    }

    return $ajax_response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getValue('card_number') || empty($form_state->getValue('card_number'))) {
      $form_state->setErrorByName('card_number', $this->t('Please enter your card number.', [], ['context' => 'egift']));
    }
    if (!$form_state->getValue('email') || empty($form_state->getValue('email'))) {
      $form_state->setErrorByName('email', $this->t('Please enter your email address.', [], ['context' => 'egift']));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
