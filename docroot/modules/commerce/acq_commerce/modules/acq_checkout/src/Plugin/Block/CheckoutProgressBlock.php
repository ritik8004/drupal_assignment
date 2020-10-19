<?php

namespace Drupal\acq_checkout\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Provides a checkout progress block.
 *
 * @Block(
 *   id = "acq_checkout_progress",
 *   admin_label = @Translation("Checkout progress"),
 *   category = @Translation("Acquia Commerce Checkout")
 * )
 */
class CheckoutProgressBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   *   The factory for configuration objects.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   ACQ Checkout Flow plugin manager object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, CartStorageInterface $cart_storage, ConfigFactoryInterface $config_factory, PluginManagerInterface $plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->cartStorage = $cart_storage;
    $this->configFactory = $config_factory;
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
    $config = $this->configFactory->get('acq_checkout.settings');
    $checkout_flow_plugin = $config->get('checkout_flow_plugin') ?: 'multistep_default';
    $checkout_flow = $this->pluginManager->createInstance($checkout_flow_plugin, []);

    // Build the steps sent to the template.
    $steps = [];
    $visible_steps = $checkout_flow->getVisibleSteps();
    $visible_step_ids = array_keys($visible_steps);
    $current_step_id = $checkout_flow->getStepId();
    $current_step_index = array_search($current_step_id, $visible_step_ids);

    // Get last step completed in the cart.
    $cart = $this->cartStorage->getCart();
    $cart_step_id = $cart->getCheckoutStep();
    $cart_step_index = array_search($cart_step_id, $visible_step_ids);

    $index = 0;
    foreach ($visible_steps as $step_id => $step_definition) {
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

      $label = $step_definition['label'];
      // Add a class if this step has been completed already.
      if ($index < $cart_step_index) {
        $completed = TRUE;
      }
      // Set the label to a link if this step has already been completed so that
      // the progress bar can be used as a sort of navigation.
      if ($index <= $cart_step_index && $index !== $current_step_index) {
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
