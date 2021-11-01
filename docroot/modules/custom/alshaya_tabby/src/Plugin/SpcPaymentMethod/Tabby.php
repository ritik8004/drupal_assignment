<?php

namespace Drupal\alshaya_tabby\Plugin\SpcPaymentMethod;

use Drupal\alshaya_acm_checkout\AlshayaBnplApiHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tabby payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "tabby",
 *   label = @Translation("Instalments with Tabby"),
 * )
 */
class Tabby extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;

  /**
   * BNPL payment method Helper.
   *
   * @var \Drupal\alshaya_acm_checkout\AlshayaBnplApiHelper
   */
  protected $alshayaBnplHelper;

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
      $container->get('alshaya_acm_checkout.bnpl_api_helper'),
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
   * @param \Drupal\alshaya_acm_checkout\AlshayaBnplApiHelper $alshaya_bnpl_helper
   *   Tabby Payment Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaBnplApiHelper $alshaya_bnpl_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaBnplHelper = $alshaya_bnpl_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    $config = $this->alshayaBnplHelper->getBnplApiConfig('tabby', 'tabby/config');
    if (empty($config['merchant_code'])) {
      $this->getLogger('tabby')->warning('Tabby status enabled but no merchant code set, ignoring.');
      return FALSE;
    }
    return TRUE;
  }

}
