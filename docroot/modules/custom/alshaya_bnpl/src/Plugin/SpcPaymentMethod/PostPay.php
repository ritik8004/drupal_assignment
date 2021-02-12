<?php

namespace Drupal\alshaya_bnpl\Plugin\SpcPaymentMethod;

use Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_bnpl\Helper\AlshayaBnplWidgetHelper;

/**
 * PostPay payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "postpay",
 *   label = @Translation("Installments with Postpay"),
 * )
 */
class PostPay extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * Postpay widget Helper.
   *
   * @var \Drupal\alshaya_bnpl\Helper\AlshayaBnplWidgetHelper
   */
  protected $alshayaBnplWidgetHelper;

  /**
   * The current route matcher service.
   *
   * @var \Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper
   */
  protected $alshayaBnplAPIHelper;

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
      $container->get('alshaya_bnpl.widget_helper'),
      $container->get('alshaya_bnpl.api_helper'),
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
   * @param \Drupal\alshaya_bnpl\Helper\AlshayaBnplWidgetHelper $bnplWidgetHelper
   *   Postpay Widget Helper.
   * @param \Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper $alshayaBnplAPIHelper
   *   Alshaya BNPL Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaBnplWidgetHelper $bnplWidgetHelper,
                              AlshayaBnplAPIHelper $alshayaBnplAPIHelper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaBnplWidgetHelper = $bnplWidgetHelper;
    $this->alshayaBnplAPIHelper = $alshayaBnplAPIHelper;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    $config = $this->alshayaBnplAPIHelper->getBnplApiConfig();
    if (!empty($config['merchant_id'])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $this->alshayaBnplWidgetHelper->getBnplBuild($build, 'checkout');
  }

}
