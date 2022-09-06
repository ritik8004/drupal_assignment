<?php

namespace Drupal\alshaya_checkout_tracker\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
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

    switch ($route_name) {
      case 'acq_cart.cart':
        $stepcount = 1;
        $bag = 'active';
        $signin = '';
        $checkout = '';
        $confirmation = '';
        break;

      case 'alshaya_spc.checkout.login':
        $stepcount = 2;
        $bag = 'completed';
        $signin = 'active';
        $checkout = '';
        $confirmation = '';
        break;

      case 'alshaya_spc.checkout':
        $stepcount = 3;
        $bag = 'completed';
        $signin = 'completed';
        $checkout = 'active';
        $confirmation = '';
        break;

      case 'alshaya_spc.checkout.confirmation':
        $stepcount = 4;
        $bag = 'completed';
        $signin = 'completed';
        $checkout = 'completed';
        $confirmation = 'active';
        break;
    }

    return [
      '#theme' => 'checkout_tracker_block',
      '#steps' => [
        'bagclass' => $bag,
        'signinclass' => $signin,
        'checkoutclass' => $checkout,
        'deliveryclass' => $confirmation,
        'stepcount' => $stepcount,
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $route_name = $this->routeMatch->getRouteName();

    // Show block for specific routes.
    return AccessResult::allowedIf(
      $route_name === 'acq_cart.cart' || $route_name === 'alshaya_spc.checkout.login' || $route_name === 'alshaya_spc.checkout' || $route_name === 'alshaya_spc.checkout.confirmation'
    );
  }

}
