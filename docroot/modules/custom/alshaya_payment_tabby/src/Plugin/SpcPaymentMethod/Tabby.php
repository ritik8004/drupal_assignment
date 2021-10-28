<?php

namespace Drupal\alshaya_payment_tabby\Plugin\SpcPaymentMethod;

use Drupal\alshaya_payment_tabby\AlshayaTabbyHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
  use StringTranslationTrait;

  /**
   * Tabby payment method Helper.
   *
   * @var \Drupal\alshaya_payment_tabby\AlshayaTabbyHelper
   */
  protected $alshayaTabbyHelper;

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
      $container->get('alshaya_payment_tabby.helper'),
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
   * @param \Drupal\alshaya_payment_tabby\AlshayaTabbyHelper $alshaya_tabby_helper
   *   Tabby Payment Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaTabbyHelper $alshaya_tabby_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaTabbyHelper = $alshaya_tabby_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    $config = $this->alshayaTabbyHelper->getTabbyApiConfig();
    if (empty($config['merchant_code'])) {
      $this->getLogger('tabby')->warning('Tabby status enabled but no merchant identifier set, ignoring.');
      return FALSE;
    }
    return TRUE;
  }

}
