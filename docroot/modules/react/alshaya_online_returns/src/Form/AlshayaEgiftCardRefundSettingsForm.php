<?php

namespace Drupal\alshaya_online_returns\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Alshaya Egift Card Refund settings.
 */
class AlshayaEgiftCardRefundSettingsForm extends ConfigFormBase {

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
    return 'alshaya_egift_card_refund_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_online_returns.egift_card_refund'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_online_returns.egift_card_refund');
    $form['egift_card_refund_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['egift_card_refund_configuration']['enable_disable_egift_card_refund'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('egift_card_refund_enabled'),
      '#title' => $this->t('Enable Egift card refund on site.'),
    ];
    $form['egift_card_refund_configuration']['egift_refund_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Egift card refund text'),
      '#description' => $this->t('Shows the message under the eGift card refund option.'),
      '#default_value' => $config->get('egift_refund_text'),
    ];
    $form['egift_card_refund_configuration']['egift_card_icon'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://',
      '#title' => $this->t('Egift Card Icon'),
      '#description' => $this->t('Icon to upload for the eGift card in refund option.'),
      '#default_value' => $config->get('egift_card_icon'),
      '#upload_validators'  => [
        'file_validate_extensions' => ['png gif jpg jpeg svg'],
      ],
    ];
    // Payment methods.
    $payment_terms = $this->checkoutOptionManager->getDefaultPayment(FALSE);
    $options = [];
    if ($payment_terms) {
      foreach ($payment_terms as $term) {
        $code = $term->get('field_payment_code')->getString();
        $options[$code] = $term->getName() . " (${code})";
      }

      $form['egift_card_refund_configuration']['not_supported_refund_payment_methods'] = [
        '#type' => 'checkboxes',
        '#options' => $options,
        '#title' => $this->t('Not supported payment methods for refund'),
        '#description' => $this->t('Select the payment methods which are not supported for eGift card refund payment.'),
        '#default_value' => $config->get('not_supported_refund_payment_methods') ?? NULL,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_online_returns.egift_card_refund')
      ->set('egift_card_refund_enabled', $form_state->getValue('enable_disable_egift_card_refund'))
      ->set('not_supported_refund_payment_methods', $form_state->getValue('not_supported_refund_payment_methods'))
      ->set('egift_refund_text', $form_state->getValue('egift_refund_text'))
      ->set('egift_card_icon', $form_state->getValue('egift_card_icon'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
