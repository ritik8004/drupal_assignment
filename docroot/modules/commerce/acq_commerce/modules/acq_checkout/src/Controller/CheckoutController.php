<?php

namespace Drupal\acq_checkout\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Provides the checkout form page.
 */
class CheckoutController implements ContainerInjectionInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The cart session.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ACQ Checkout Flow plugin manager object.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Constructs a new CheckoutController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   ACQ Checkout Flow plugin manager object.
   */
  public function __construct(FormBuilderInterface $form_builder, CartStorageInterface $cart_storage, ConfigFactoryInterface $config_factory, PluginManagerInterface $plugin_manager) {
    $this->formBuilder = $form_builder;
    $this->cartStorage = $cart_storage;
    $this->configFactory = $config_factory;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('acq_cart.cart_storage'),
      $container->get('config.factory'),
      $container->get('plugin.manager.acq_checkout_flow')
    );
  }

  /**
   * Builds and processes the form provided by the order's checkout flow.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return array
   *   The render form.
   */
  public function formPage(RouteMatchInterface $route_match) {
    // @TODO: Create backend configuration form for this.
    $form_state = new FormState();
    $config = $this->configFactory->get('acq_checkout.settings');
    $checkoutFlowPlugin = $config->get('checkout_flow_plugin') ?: 'multistep_default';
    $type = $this->pluginManager->createInstance($checkoutFlowPlugin, ['validate_current_step' => TRUE]);
    return $this->formBuilder->buildForm($type, $form_state);
  }

  /**
   * Checks access for the form page.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function checkAccess(RouteMatchInterface $route_match, AccountInterface $account) {
    // @TODO: Add additional access checking.
    $access = AccessResult::allowedIfHasPermission($account, 'access checkout');
    return $access;
  }

}
