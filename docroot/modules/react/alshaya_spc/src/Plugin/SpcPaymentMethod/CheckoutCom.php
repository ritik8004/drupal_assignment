<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
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
    // @TODO: Add configuration for this and use live for live.
    $build['#attached']['library'][] = 'alshaya_spc/checkout_sandbox_kit';

    $build['#attached']['drupalSettings']['checkoutCom'] = [
      'always_3d' => TRUE,
      'process_mada' => TRUE,
      'tokenize' => TRUE,
      'debugMode' => TRUE,
      'publicKey' => $this->checkoutComApiHelper->getCheckoutcomConfig('public_key'),
      'tokenizedCards' => alshaya_acm_customer_is_customer($this->currentUser)
      ? $this->checkoutComApiHelper->getCustomerCards($this->currentUser)
      : [],
    ];
  }

}
