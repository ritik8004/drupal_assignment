<?php

namespace Drupal\alshaya_checkout_tracker\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Cache\Cache;
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

    $stepMap = [
      'acq_cart.cart' => [
        'label' => $this->t('Bag', [], ['context' => 'alshaya_checkout_tracker']),
        'stepcount' => 1,
        'url' => Url::fromRoute('acq_cart.cart'),
      ],
      'alshaya_spc.checkout.login' => [
        'label' => $this->t('Sign in', [], ['context' => 'alshaya_checkout_tracker']),
        'stepcount' => 2,
        'url' => Url::fromRoute('alshaya_spc.checkout.login'),
      ],
      'alshaya_spc.checkout' => [
        'label' => $this->t('Checkout', [], ['context' => 'alshaya_checkout_tracker']),
        'stepcount' => 3,
        'url' => Url::fromRoute('alshaya_spc.checkout'),
      ],
      'alshaya_spc.checkout.confirmation' => [
        'label' => $this->t('Confirmation', [], ['context' => 'alshaya_checkout_tracker']),
        'stepcount' => 4,
      ],
    ];

    return [
      '#attached' => [
        'library' => [
          'alshaya_white_label/checkout-tracker',
        ],
      ],
      '#theme' => 'checkout_tracker_block',
      '#stepMap' => $stepMap,
      '#activeMapKey' => $route_name,
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Vary based on route.
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $route_name = $this->routeMatch->getRouteName();
    // Show block for specific routes.
    return AccessResult::allowedIf(
      in_array($route_name, [
        'acq_cart.cart',
        'alshaya_spc.checkout.login',
        'alshaya_spc.checkout',
        'alshaya_spc.checkout.confirmation',
      ])
    );
  }

}
