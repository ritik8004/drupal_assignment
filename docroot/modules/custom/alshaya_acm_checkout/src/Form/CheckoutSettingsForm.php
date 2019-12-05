<?php

namespace Drupal\alshaya_acm_checkout\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CheckoutSettingsForm.
 */
class CheckoutSettingsForm extends ConfigFormBase {

  /**
   * Checkout options manager.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager
   */
  protected $checkoutOptionManager;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'checkout_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_checkout.settings'];
  }

  /**
   * CheckoutSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_option_manager
   *   Checkout option manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CheckoutOptionsManager $checkout_option_manager) {
    parent::__construct($config_factory);
    $this->configFactory = $config_factory;
    $this->checkoutOptionManager = $checkout_option_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('alshaya_acm_checkout.options_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_checkout.settings');
    $config->set('checkout_guest_summary', $form_state->getValue('checkout_guest_summary'));
    $config->set('checkout_guest_email_usage', $form_state->getValue('checkout_guest_email_usage'));
    $config->set('checkout_guest_login', $form_state->getValue('checkout_guest_login'));
    $config->set('checkout_terms_condition', $form_state->getValue('checkout_terms_condition'));
    $config->set('checkout_customer_service', $form_state->getValue('checkout_customer_service'));
    $config->set('click_collect_method_method_code', $form_state->getValue('click_collect_method_method_code'));
    $config->set('click_collect_method_carrier_code', $form_state->getValue('click_collect_method_carrier_code'));
    $config->set('checkout_display_magento_error', $form_state->getValue('checkout_display_magento_error'));
    $config->set('cod_surcharge_label', $form_state->getValue('cod_surcharge_label'));
    $config->set('cod_surcharge_short_description', $form_state->getValue('cod_surcharge_short_description'));
    $config->set('cod_surcharge_description', $form_state->getValue('cod_surcharge_description'));
    $config->set('cod_surcharge_tooltip', $form_state->getValue('cod_surcharge_tooltip'));
    $config->set('exclude_payment_methods', $form_state->getValue('exclude_payment_methods'));
    $config->set('cancel_reservation_enabled', $form_state->getValue('cancel_reservation_enabled'));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_checkout.settings');

    $form['checkout_guest_summary'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Checkout as guest summary'),
      '#default_value' => $config->get('checkout_guest_summary.value'),
    ];

    $form['checkout_guest_email_usage'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Guest email usage description'),
      '#default_value' => $config->get('checkout_guest_email_usage.value'),
    ];

    $form['checkout_guest_login'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Checkout login help'),
      '#default_value' => $config->get('checkout_guest_login.value'),
    ];

    $form['checkout_terms_condition'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Checkout Terms and Conditions'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_terms_condition.value'),
    ];

    $form['checkout_customer_service'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Checkout Customer Service'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_customer_service.value'),
    ];

    $form['click_collect_method_method_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Click and Collect delivery method - method code'),
      '#required' => TRUE,
      '#default_value' => $config->get('click_collect_method_method_code'),
    ];

    $form['click_collect_method_carrier_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Click and Collect delivery method - carrier code'),
      '#required' => TRUE,
      '#default_value' => $config->get('click_collect_method_carrier_code'),
    ];

    $form['checkout_display_magento_error'] = [
      '#type' => 'select',
      '#options' => [
        0 => $this->t('No - Generic message'),
        1 => $this->t('Yes'),
      ],
      '#title' => $this->t('Display error message from magento'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_display_magento_error'),
    ];

    $form['cod_surcharge'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('COD Surcharge'),
      '#tree' => FALSE,
    ];

    $form['cod_surcharge']['cod_surcharge_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('Label to use in totals section.'),
      '#required' => TRUE,
      '#default_value' => $config->get('cod_surcharge_label'),
    ];

    $form['cod_surcharge']['cod_surcharge_short_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Short Description'),
      '#description' => $this->t('Description to display on payment methods section. Use [surcharge] as placeholder where you want to display amount with currency code.'),
      '#required' => TRUE,
      '#default_value' => $config->get('cod_surcharge_short_description'),
    ];

    $form['cod_surcharge']['cod_surcharge_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('Description to display on payment methods section with CoD is selected. Use [surcharge] as placeholder where you want to display amount with currency code.'),
      '#required' => TRUE,
      '#default_value' => $config->get('cod_surcharge_description'),
    ];

    $form['cod_surcharge']['cod_surcharge_tooltip'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tooltip'),
      '#description' => $this->t('Description to show as tooltip.'),
      '#required' => TRUE,
      '#default_value' => $config->get('cod_surcharge_tooltip'),
    ];

    $form['payment_methods_exclude'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Exclude payment methods'),
      '#tree' => FALSE,
    ];
    $payment_terms = $this->checkoutOptionManager->getDefaultPayment(FALSE);
    $options = [];
    foreach ($payment_terms as $term) {
      $options[$term->get('field_payment_code')->first()->getString()] = $term->getName();
    }
    $form['payment_methods_exclude']['exclude_payment_methods'] = [
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => $this->t('Exclude payment methods'),
      '#description' => $this->t('Select the payment methods which needs to be exclude on payment screen.'),
      '#default_value' => $config->get('exclude_payment_methods'),
    ];

    $form['cancel_reservation_enabled'] = [
      '#type' => 'select',
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#title' => $this->t('Invoke Cancel Reservation API?'),
      '#description' => $this->t('Flag to specify if Drupal should invoke the cancel reservation API or not.'),
      '#required' => TRUE,
      '#default_value' => $config->get('cancel_reservation_enabled') ?? 0,
    ];

    return $form;
  }

}
