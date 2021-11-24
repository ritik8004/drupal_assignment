<?php

namespace Drupal\alshaya_egift_card\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Alshaya Egift Card settings.
 */
class AlshayaEgiftCardSettingsForm extends ConfigFormBase {

  /**
   * Checkout options manager.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager
   */
  protected $checkoutOptionManager;

  /**
   * AlshayaEgiftCardSettingsForm constructor.
   *
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_option_manager
   *   Checkout option manager.
   */
  public function __construct(CheckoutOptionsManager $checkout_option_manager) {
    $this->checkoutOptionManager = $checkout_option_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_checkout.options_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_egift_card_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_egift_card.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_egift_card.settings');
    $form['egift_card_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['egift_card_configuration']['enable_disable_egift_card'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('egift_card_enabled'),
      '#title' => $this->t('Enable Egift card on site.'),
    ];
    // Payment methods.
    $payment_terms = $this->checkoutOptionManager->getDefaultPayment(FALSE);
    $options = [];
    if ($payment_terms) {
      foreach ($payment_terms as $term) {
        $code = $term->get('field_payment_code')->getString();
        $options[$code] = $term->getName() . " (${code})";
      }

      $form['egift_card_configuration']['payment_methods_not_supported'] = [
        '#type' => 'checkboxes',
        '#options' => $options,
        '#title' => $this->t('Not supported payment methods'),
        '#description' => $this->t('Select the payment methods which are not supported for eGift cart payment.'),
        '#default_value' => $config->get('payment_methods_not_supported') ?? NULL,
      ];
    }
    $form['egift_card_configuration']['topup_terms_conditions_text'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Topup: Terms and conditions block text'),
      '#required' => TRUE,
      '#default_value' => $config->get('topup_terms_conditions_text.value'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_egift_card.settings')
      ->set('egift_card_enabled', $form_state->getValue('enable_disable_egift_card'))
      ->set('payment_methods_not_supported', $form_state->getValue('payment_methods_not_supported'))
      ->set('topup_terms_conditions_text', $form_state->getValue('topup_terms_conditions_text'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
