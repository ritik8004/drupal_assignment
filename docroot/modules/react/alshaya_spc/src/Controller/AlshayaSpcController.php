<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaSpcController.
 */
class AlshayaSpcController extends ControllerBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Chekcout option manager.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager
   */
  protected $checkoutOptionManager;

  /**
   * Payment method.
   *
   * @var \Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager
   */
  protected $paymentMethodManager;

  /**
   * AlshayaSpcController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager $payment_method_manager
   *   Payment method manager.
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager
   *   Checkout option manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              AlshayaSpcPaymentMethodManager $payment_method_manager,
                              CheckoutOptionsManager $checkout_options_manager) {
    $this->configFactory = $config_factory;
    $this->checkoutOptionManager = $checkout_options_manager;
    $this->paymentMethodManager = $payment_method_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.alshaya_spc_payment_method'),
      $container->get('alshaya_acm_checkout.options_manager')
    );
  }

  /**
   * Overridden controller for cart page.
   *
   * @return array
   *   Markup for cart page.
   */
  public function cart() {
    return [
      '#type' => 'markup',
      '#markup' => '<div id="spc-cart"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/cart',
          'alshaya_spc/cart-sticky-header',
          'alshaya_white_label/spc-utils',
          'alshaya_white_label/spc-cart',
        ],
      ],
    ];
  }

  /**
   * Checkout pages.
   *
   * @return array
   *   Markup for checkout page.
   */
  public function checkout() {
    $cc_config = $this->configFactory->get('alshaya_click_collect.settings');
    $checkout_settings = $this->configFactory->get('alshaya_acm_checkout.settings');

    // Get payment methods.
    $payment_methods = [];
    foreach ($this->paymentMethodManager->getDefinitions() ?? [] as $payment_method) {
      $payment_method_term = $this->checkoutOptionManager->loadPaymentMethod(
        $payment_method['id'],
        $payment_method['label']->render()
      );
      $payment_methods[$payment_method['id']] = [
        'name' => $payment_method_term->getName(),
        'description' => $payment_method_term->getDescription(),
        'code' => $payment_method_term->get('field_payment_code')->getString(),
        'default' => ($payment_method_term->get('field_payment_default')->getString() == '1'),
        'has_form' => $payment_method['hasForm'],
      ];
    }

    return [
      '#type' => 'markup',
      '#markup' => '<div id="spc-checkout"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/checkout',
          'alshaya_white_label/spc-utils',
        ],
        'drupalSettings' => [
          'cnc_subtitle_available' => $cc_config->get('checkout_click_collect_available'),
          'cnc_subtitle_unavailable' => $cc_config->get('checkout_click_collect_unavailable'),
          'terms_condition' => $checkout_settings->get('checkout_terms_condition.value'),
          'payment_methods' => $payment_methods,
        ],
      ],
    ];
  }

}
