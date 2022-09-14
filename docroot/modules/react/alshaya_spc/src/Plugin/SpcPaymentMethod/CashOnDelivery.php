<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * COD payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "cashondelivery",
 *   label = @Translation("Cash on Delivery"),
 *   hasForm = false
 * )
 */
class CashOnDelivery extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * CashOnDelivery constructor.
   *
   * @param array $configuration
   *   Plugin configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $build['#strings'] = array_merge($build['#strings'], self::getCodSurchargeStrings());

    // Get COD payment method mobile verification settings.
    $cod_mobile_verification = self::getCodMobileVerificationSettings();
    if ($cod_mobile_verification) {
      $build['#attached']['drupalSettings']['codMobileVerification'] = $cod_mobile_verification;
      $build['#attached']['library'][] = 'alshaya_white_label/checkout-cod-mobile-verification';
    }
  }

  /**
   * Strings related to COD.
   *
   * @return array
   *   Translated strings array.
   */
  public function getCodSurchargeStrings(): array {
    $strings = [];

    $checkout_settings = $this->configFactory->get('alshaya_acm_checkout.settings');

    $string_keys = [
      'cod_surcharge_label',
      'cod_surcharge_description',
      'cod_surcharge_short_description',
      'cod_surcharge_tooltip',
    ];

    foreach ($string_keys as $key) {
      $strings[] = [
        'key' => $key,
        'value' => trim(preg_replace("/[\r\n]+/", '', $checkout_settings->get($key))),
      ];
    }

    return $strings;
  }

  /**
   * Get COD payment method settings for mobile verification.
   *
   * @return bool
   *   Configuration value.
   */
  public function getCodMobileVerificationSettings(): bool {
    return $this->configFactory->get('alshaya_spc.settings')->get('cod_mobile_verification');
  }

}
