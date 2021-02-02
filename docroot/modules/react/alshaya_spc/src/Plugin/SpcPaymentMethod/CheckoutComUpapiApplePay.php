<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checkout.com UPAPI Apple Pay payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_upapi_applepay",
 *   label = @Translation("ApplePay (Checkout.com)"),
 * )
 */
class CheckoutComUpapiApplePay extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Checkout.com API Helper.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $checkoutComApiHelper;

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
      $container->get('acq_checkoutcom.agent_api')
    );
  }

  /**
   * CheckoutComApplePay constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\acq_checkoutcom\ApiHelper $checkout_com_api_helper
   *   Checkout.com API Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ApiHelper $checkout_com_api_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->checkoutComApiHelper = $checkout_com_api_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {

  }

}
