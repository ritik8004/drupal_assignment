<?php

namespace Drupal\alshaya_aura_react\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides My Accounts AURA block.
 *
 * @Block(
 *   id = "my_accounts_aura_block",
 *   admin_label = @Translation("My Accounts AURA block")
 * )
 */
class MyAccountsAuraBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * AuraRewardsHeader constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div id="my-accounts-aura"></div>',
      '#attached' => [
        'library' => [
          'alshaya_white_label/aura-loyalty-myaccount',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $route_name = $this->routeMatch->getRouteName();

    // Show block only on my accounts page.
    return AccessResult::allowedIf($route_name === 'entity.user.canonical');
  }

}
