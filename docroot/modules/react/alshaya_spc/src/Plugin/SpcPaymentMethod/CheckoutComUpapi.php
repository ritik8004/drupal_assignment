<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checkout.com UPAPI payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_upapi",
 *   label = @Translation("Credit / Debit Card"),
 * )
 */
class CheckoutComUpapi extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * API Wrapper.
   *
   * @var \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper
   */
  protected $apiWrapper;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
      $container->get('alshaya_acm_checkoutcom.api_helper'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
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
   * @param \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper $api_wrapper
   *   API Wrapper.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaAcmCheckoutComAPIHelper $api_wrapper,
                              AccountInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->apiWrapper = $api_wrapper;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    $config = $this->apiWrapper->getCheckoutcomUpApiConfig();

    if (empty($config) || empty($config['public_key'])) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $config = $this->apiWrapper->getCheckoutcomUpApiConfig();

    $build['#cache']['contexts'] = ['user'];
    $build['#cache']['tags'] = ['user:' . $this->currentUser->id()];

    $api_url = $config['api_url'] ?? 'https://api.sandbox.checkout.com';
    $api_url = trim($api_url, '/');

    $allowed_cards = explode(',', strtolower($config['allowed_card_types']));
    $allowed_cards_mapped = [];
    $allowed_cards_mapping = Settings::get('checkout_com_upapi_accepted_cards_mapping', []);
    foreach ($allowed_cards as $allowed_card) {
      $allowed_cards_mapped[$allowed_card] = $allowed_cards_mapping[$allowed_card] ?? '';
    }

    $tokenize = FALSE;
    $tokenizedCards = [];
    $customer_id = $this->getCustomerId();
    if ($config['vault_enabled'] && $customer_id > 0) {
      $tokenize = TRUE;
      $tokenizedCards = $this->apiWrapper->getSavedCards($customer_id);
    }

    $build['#attached']['drupalSettings']['checkoutComUpapi'] = [
      'acceptedCards' => array_values(array_filter($allowed_cards_mapped)),
      'publicKey' => $config['public_key'],
      'apiUrl' => $api_url . '/tokens',
      'tokenizedCards' => $tokenizedCards,
      'tokenize' => $tokenize,
      'cvvCheck' => $config['cvv_check'],
      'processMada' => in_array('mada', $allowed_cards),
    ];

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

    $build['#strings']['checkout_com_upapi_error_info'] = [
      'key' => 'checkout_com_upapi_error_info',
      'value' => $this->t('Order ID: @order_id'),
    ];
  }

  /**
   * Wrapper function to get Customer ID of the User.
   *
   * @return int
   *   Customer ID of the user.
   */
  protected function getCustomerId() {
    if ($this->currentUser->isAnonymous()) {
      return 0;
    }

    $user = $this->entityTypeManager
      ->getStorage('user')
      ->load($this->currentUser->id());

    return (int) $user->get('acq_customer_id')->getString();
  }

}
