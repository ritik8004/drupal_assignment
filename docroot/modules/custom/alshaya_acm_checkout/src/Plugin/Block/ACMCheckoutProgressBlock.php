<?php

namespace Drupal\alshaya_acm_checkout\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a checkout progress block.
 *
 * @Block(
 *   id = "acm_checkout_progress",
 *   admin_label = @Translation("Checkout progress"),
 *   category = @Translation("ACM Checkout")
 * )
 */
class ACMCheckoutProgressBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The cart session.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Constructs a new CheckoutProgressBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, CartStorageInterface $cart_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->cartStorage = $cart_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('acq_cart.cart_storage')
    );
  }

  /**
   * Builds the checkout progress block.
   *
   * @return array
   *   A render array.
   */
  public function build() {
    $route_name = $this->routeMatch->getRouteName();
    if ($route_name !== 'acq_checkout.form') {
      return [];
    }

    // Load the CheckoutFlow plugin.
    $config = \Drupal::config('acq_checkout.settings');
    $checkout_flow_plugin = $config->get('checkout_flow_plugin') ?: 'multistep_default';
    $plugin_manager = \Drupal::service('plugin.manager.acq_checkout_flow');
    $checkout_flow = $plugin_manager->createInstance($checkout_flow_plugin, []);

    $current_step_id = $checkout_flow->getStepId();

    if ($current_step_id == 'login') {
      return [];
    }

    // Build the steps sent to the template.
    $steps = [];
    $visible_steps = $checkout_flow->getVisibleSteps();

    // Login step not required for progress block.
    unset($visible_steps['login']);

    $visible_step_ids = array_keys($visible_steps);

    $current_step_index = array_search($current_step_id, $visible_step_ids);

    // Get last step completed in the cart.
    if ($cart = $this->cartStorage->getCart(FALSE)) {
      $cart_step_id = $cart->getCheckoutStep();
    }
    else {
      // Confirmation is the only step where we won't have cart.
      $cart_step_id = 'confirmation';
    }

    $cart_step_index = array_search($cart_step_id, $visible_step_ids);

    $index = 0;
    foreach ($visible_steps as $step_id => $step_definition) {
      if ($step_id == 'login') {
        continue;
      }
      $is_link = FALSE;
      $completed = FALSE;
      if ($index < $current_step_index) {
        $position = 'previous';
      }
      elseif ($index == $current_step_index) {
        $position = 'current';
      }
      else {
        $position = 'next';
      }

      $label = $step_definition['title'];
      // Add a class if this step has been completed already.
      if ($index < $cart_step_index) {
        $completed = TRUE;
      }
      // Set the label to a link if this step has already been completed so that
      // the progress bar can be used as a sort of navigation.
      if ($current_step_id != 'confirmation' && $index <= $cart_step_index && $index !== $current_step_index) {
        $is_link = TRUE;
        $label = Link::createFromRoute($label, 'acq_checkout.form', [
          'step' => $step_id,
        ])->toString();
      }

      $steps[] = [
        'id' => $step_id,
        'label' => $label,
        'position' => $position,
        'completed' => $completed,
        'is_link' => $is_link,
      ];

      $index++;
    }

    return [
      '#attached' => [
        'library' => ['acq_checkout/checkout_progress'],
      ],
      '#theme' => 'acq_checkout_progress',
      '#steps' => $steps,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
