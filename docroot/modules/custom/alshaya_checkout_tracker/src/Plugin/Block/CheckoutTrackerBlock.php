<?php

namespace Drupal\alshaya_checkout_tracker\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_checkout_tracker\Helper\CheckoutTrackerHelper;

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
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Checkout Helper service object.
   *
   * @var Drupal\alshaya_checkout_tracker\Helper\CheckoutTrackerHelper
   */
  protected $checkoutTrackerHelper;

  /**
   * Constructs a new CheckoutTrackerBlock plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   Private temp store service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param Drupal\alshaya_checkout_tracker\Helper\CheckoutTrackerHelper $checkoutTrackerHelper
   *   The Checkout Tracker service.
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
  AccountProxyInterface $current_user,
  RouteMatchInterface $route_match,
  CheckoutTrackerHelper $checkoutTrackerHelper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->routeMatch = $route_match;
    $this->checkoutTrackerHelper = $checkoutTrackerHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('alshaya_checkout_tracker.checkout_tracker_helper')
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
        'label' => $this->t('Delivery and Payment', [], ['context' => 'alshaya_checkout_tracker']),
        'stepcount' => 3,
        'url' => ($this->currentUser->isAuthenticated()) ? Url::fromRoute('alshaya_spc.checkout') : '',
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
          'alshaya_checkout_tracker/alshaya_checkout_tracker',
        ],
      ],
      '#theme' => 'checkout_tracker_block',
      '#stepMap' => $stepMap,
      '#activeMapKey' => $route_name,
      '#attributes' => [
        'class' => ($route_name == 'acq_cart.cart') ? ['hide-checkout-tracker'] : '',
      ],
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
      $this->checkoutTrackerHelper->isCheckoutTrackerEnabled() &&
      in_array($route_name, [
        'acq_cart.cart',
        'alshaya_spc.checkout.login',
        'alshaya_spc.checkout',
        'alshaya_spc.checkout.confirmation',
      ])
    )->addCacheableDependency($this->checkoutTrackerHelper->getCacheTags());
  }

}
