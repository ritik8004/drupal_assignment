<?php

namespace Drupal\alshaya_acm_checkout\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * Checkout settings config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * ACQ Checkout Flow plugin manager object.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory object.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   ACQ Checkout Flow plugin manager object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              RouteMatchInterface $route_match,
                              CartStorageInterface $cart_storage,
                              ConfigFactoryInterface $config_factory,
                              PluginManagerInterface $plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->cartStorage = $cart_storage;
    $this->config = $config_factory->get('acq_checkout.settings');
    $this->pluginManager = $plugin_manager;
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
      $container->get('acq_cart.cart_storage'),
      $container->get('config.factory'),
      $container->get('plugin.manager.acq_checkout_flow')
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
    $checkout_flow_plugin = $this->config->get('checkout_flow_plugin') ?: 'multistep_default';
    $checkout_flow = $this->pluginManager->createInstance($checkout_flow_plugin, []);

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

    // Confirmation is only step where we wont have cart, we use it by default.
    $cart_step_id = 'confirmation';

    // Get last step completed in the cart.
    if ($cart = $this->cartStorage->getCart(FALSE)) {
      $cart_step_id = $cart->getCheckoutStep();
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
  public function getCacheContexts() {
    // Vary based on cart id and route.
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'session',
      'cookies:Drupal_visitor_acq_cart_id',
      'route',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();

    // As soon as we have cart, we have session.
    // As soon as we have session, varnish is disabled.
    // We are good to have no cache tag based on cart if there is none.
    if ($cart = $this->cartStorage->getCart(FALSE)) {
      // Custom cache tag here will be cleared in API Wrapper after every
      // update cart call.
      $cache_tags = Cache::mergeTags($cache_tags, [
        'cart:' . $cart->id(),
      ]);
    }

    return $cache_tags;
  }

}
