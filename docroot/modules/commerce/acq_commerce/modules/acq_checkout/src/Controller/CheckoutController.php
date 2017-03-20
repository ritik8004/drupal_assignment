<?php

/**
 * @file
 * Contains \Drupal\acq_checkout\Controller\CheckoutController.
 */

namespace Drupal\acq_checkout\Controller;

use Drupal\acq_checkout\CheckoutFlowPluginCollection;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Constructs a new CheckoutController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   */
  public function __construct(FormBuilderInterface $form_builder, CartStorageInterface $cart_storage) {
    $this->formBuilder = $form_builder;
    $this->cartStorage = $cart_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('acq_cart.cart_storage')
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
    $config = \Drupal::config('acq_checkout.settings');
    $checkoutFlowPlugin = $config->get('checkout_flow_plugin') ?: 'multistep_default';
    $plugin_manager = \Drupal::service('plugin.manager.acq_checkout_flow');
    $type = $plugin_manager->createInstance($checkoutFlowPlugin, ['validate_current_step' => TRUE]);
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
