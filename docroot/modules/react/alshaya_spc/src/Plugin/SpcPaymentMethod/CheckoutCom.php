<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checkout.com payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com",
 *   label = @Translation("Credit / Debit Card"),
 * )
 */
class CheckoutCom extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Checkout.com API Helper.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $checkoutComApiHelper;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('acq_checkoutcom.agent_api'),
      $container->get('current_user')
    );
  }

  /**
   * CheckoutCom constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\acq_checkoutcom\ApiHelper $checkout_com_api_helper
   *   Checkout.com API Helper.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ApiHelper $checkout_com_api_helper,
                              AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->checkoutComApiHelper = $checkout_com_api_helper;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $sandbox = ($this->checkoutComApiHelper->getCheckoutcomConfig('environment') === 'sandbox');
    $build['#cache']['contexts'] = ['user'];
    $build['#cache']['tags'] = ['user:' . $this->currentUser->id()];
    $build['#attached']['drupalSettings']['checkoutCom'] = [
      'enforce3d' => $this->checkoutComApiHelper->getCheckoutcomConfig('verify3dsecure'),
      'processMada' => $this->checkoutComApiHelper->getCheckoutcomConfig('mada_enabled'),
      'tokenize' => $this->checkoutComApiHelper->getCheckoutcomConfig('vault_enabled'),
      'publicKey' => $this->checkoutComApiHelper->getCheckoutcomConfig('public_key'),
      'debugMode' => $sandbox,
      'tokenizedCards' => alshaya_acm_customer_is_customer($this->currentUser)
      ? $this->customerCardWithFilteredFields()
      : [],
    ];

    $build['#attached']['library'][] = $sandbox
      ? 'alshaya_spc/checkout_sandbox_kit'
      : 'alshaya_spc/checkout_live_kit';

    $build['#attached']['library'][] = 'alshaya_white_label/secure-text';

    $build['#strings']['invalid_card'] = [
      'key' => 'invalid_card',
      'value' => $this->t('Invalid Debit / Credit Card number'),
    ];

    $build['#strings']['invalid_expiry'] = [
      'key' => 'invalid_expiry',
      'value' => $this->t('Incorrect credit card expiration date'),
    ];

    $build['#strings']['invalid_cvv'] = [
      'key' => 'invalid_cvv',
      'value' => $this->t('Invalid security code (CVV)'),
    ];
  }

  /**
   * Expose only required keys in drupalSettings for tokenizedCards.
   *
   * @return array
   *   Return array of tokenized cards.
   */
  protected function customerCardWithFilteredFields() {
    $card_with_required_keys = [];
    try {
      $required_keys = [
        'public_hash',
        'paymentMethod',
        'maskedCC',
        'expirationDate',
        'mada',
      ];
      $cards = $this->checkoutComApiHelper->getCustomerCards($this->currentUser);
      foreach ($cards as $card_hash => $card) {
        // Get only $required_keys from $card.
        $card_with_required_keys[$card_hash] = array_intersect_key(
          $card,
          array_flip($required_keys)
        );
      }
    }
    catch (\Exception $e) {
    }
    return $card_with_required_keys;
  }

}
