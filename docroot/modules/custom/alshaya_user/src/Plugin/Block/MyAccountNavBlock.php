<?php

namespace Drupal\alshaya_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides 'my account nav' block.
 *
 * @Block(
 *   id = "alshaya_my_account_nav",
 *   admin_label = @Translation("Alshaya my account navigation")
 * )
 */
class MyAccountNavBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRoute;

  /**
   * MyAccountNavBlock constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_account
   *   The current account object.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route
   *   The current route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_account, CurrentRouteMatch $current_route) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
    $this->currentRoute = $current_route;
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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $items = [];

    // Current route name.
    $route_name = $this->currentRoute->getRouteName();

    // My accounts routes links list.
    $my_account_routes = [
      'entity.user.canonical' => [
        $this->t('Home'), $this->t('My Account'),
      ],
      'acq_customer.orders' => [
        $this->t('Home'), $this->t('My Account'), $this->t('Orders'),
      ],
      'entity.user.edit_form' => [
        $this->t('Home'), $this->t('My Account'),
      ],
      'entity.profile.type.address_book.user_profile_form' => [
        $this->t('Home'), $this->t('My Account'), $this->t('Address Book'),
      ],
      'entity.user.canonical' => [
        $this->t('Home'), $this->t('My Account'),
      ],
      'entity.user.canonical' => [
        $this->t('Home'), $this->t('My Account'),
      ],
    ];

    if (isset($my_account_routes[$route_name])) {
      $items = $my_account_routes[$route_name];
    }

    $build = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
      '#attributes' => [
        'class' => [
          'my-account-nav-bar',
        ],
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
