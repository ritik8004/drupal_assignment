<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fawry payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_upapi_fawry",
 *   label = @Translation("Fawry (Checkout.com)"),
 *   hasForm = false
 * )
 */
class CheckoutComUpapiFawry extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * API Wrapper.
   *
   * @var \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper
   */
  protected $apiWrapper;

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
      $container->get('alshaya_acm_checkoutcom.api_helper')
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
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaAcmCheckoutComAPIHelper $api_wrapper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->apiWrapper = $api_wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $strings = [
      [
        'key' => 'fawry_payment_option_prefix_description',
        'value' => $this->t('You’ll receive your Fawry reference number on the contact details below once you’ve placed your order.​'),
      ],
      [
        'key' => 'fawry_payment_option_suffix_description',
        'value' => $this->t("Pay for your order through any of <a href='#' target='_blank'>Fawry's cash points</a> at your convenient time and location across Egypt."),
      ],
      [
        'key' => 'fawry_checkout_confirmation_message_prefix',
        'value' => $this->t('Cash payment with Fawry'),
      ],
      [
        'key' => 'fawry_checkout_confirmation_message',
        'value' => $this->t('Amount due - @amount. Please complete your payment at the nearest Fawry cash point using your reference #@reference_no by @date_and_time.​'),
      ],
    ];

    $build['#strings'] = array_merge($build['#strings'], $strings);

    $config = $this->apiWrapper->getCheckoutcomUpApiConfig();
    $build['#attached']['drupalSettings']['checkoutComUpapiFawry'] = [
      'fawry_expiry_time' => $config['fawry_expiry_time'],
    ];
  }

}
