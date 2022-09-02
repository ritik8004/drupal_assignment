<?php

namespace Drupal\alshaya_checkout_tracker\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CheckoutTrackerBlock' block.
 *
 * @Block(
 *   id = "checkout_tracker_block",
 *   admin_label = @Translation("Checkout tracker block"),
 * )
 */
class CheckoutTrackerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new CheckoutTrackerBlock plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   Private temp store service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->configFactory = $config_factory;
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
      $container->get('config.factory')
    );
  }

  /**
   * Implements \Drupal\block\BlockBase::build().
   */
  public function build() {
    $route_name = $this->routeMatch->getRouteName();
    $bag = NULL;
    $signin = NULL;
    $checkout = NULL;
    $confirmation = NULL;

    if ($route_name == 'acq_cart.cart') {
      $bag = 'current';
      $signin = '';
      $checkout = '';
      $confirmation = '';
    }
    if ($route_name == 'alshaya_spc.checkout.login') {
      $bag = 'completed';
      $signin = 'current';
      $checkout = '';
      $confirmation = '';
    }
    if ($route_name == 'alshaya_spc.checkout') {
      $bag = 'completed';
      $signin = 'completed';
      $checkout = 'current';
      $confirmation = '';
    }
    if ($route_name == 'alshaya_spc.checkout.confirmation') {
      $bag = 'completed';
      $signin = 'completed';
      $checkout = 'completed';
      $confirmation = 'current';
    }

    return [
      '#theme' => 'checkout_tracker_block',
      '#steps' => [
        'bagclass' => $bag,
        'signinclass' => $signin,
        'checkoutclass' => $checkout,
        'deliveryclass' => $confirmation,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
