<?php

namespace Drupal\alshaya_aura_react\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_aura_react\Helper\AuraHelper;

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
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Aura Helper service object.
   *
   * @var Drupal\alshaya_aura_react\Helper\AuraHelper
   */
  protected $auraHelper;

  /**
   * MyAccountsAuraBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param Drupal\alshaya_aura_react\Helper\AuraHelper $aura_helper
   *   The aura helper service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              RouteMatchInterface $route_match,
                              ModuleHandlerInterface $module_handler,
                              AuraHelper $aura_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
    $this->auraHelper = $aura_helper;
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
      $container->get('current_route_match'),
      $container->get('module_handler'),
      $container->get('alshaya_aura_react.aura_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->moduleHandler->loadInclude('alshaya_aura_react', 'inc', 'alshaya_aura_react.static_strings');
    return [
      '#theme' => 'my_accounts_aura_block',
      '#strings' => _alshaya_aura_static_strings(),
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

    // Show block only on my accounts page if aura is enabled.
    return AccessResult::allowedIf(
      $this->auraHelper->isAuraEnabled() && $route_name === 'entity.user.canonical'
    );
  }

}
