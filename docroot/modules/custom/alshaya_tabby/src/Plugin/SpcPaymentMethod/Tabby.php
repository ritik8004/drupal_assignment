<?php

namespace Drupal\alshaya_tabby\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\alshaya_tabby\AlshayaTabbyApiHelper;
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
   * Tabby payment method Helper.
   *
   * @var \Drupal\alshaya_tabby\AlshayaTabbyApiHelper
   */
  protected $tabbyApiHelper;

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
      $container->get('alshaya_tabby.api_helper'),
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
   * @param \Drupal\alshaya_tabby\AlshayaTabbyApiHelper $tabby_api_helper
   *   Tabby api Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaTabbyApiHelper $tabby_api_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tabbyApiHelper = $tabby_api_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    $config = $this->tabbyApiHelper->getTabbyApiConfig();
    if (empty($config['merchant_code'])) {
      $this->getLogger('tabby')->warning('Tabby status enabled but no merchant code set, ignoring.');
      return FALSE;
    }
    return TRUE;
  }

}
