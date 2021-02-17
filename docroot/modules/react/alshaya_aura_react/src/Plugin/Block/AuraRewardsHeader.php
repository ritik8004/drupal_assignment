<?php

namespace Drupal\alshaya_aura_react\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_aura_react\Helper\AuraHelper;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides 'AURA Rewards Header' block.
 *
 * @Block(
 *   id = "aura_rewards_header",
 *   admin_label = @Translation("AURA Rewards Header")
 * )
 */
class AuraRewardsHeader extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * AuraRewardsHeader constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param Drupal\alshaya_aura_react\Helper\AuraHelper $aura_helper
   *   The aura helper service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              ModuleHandlerInterface $module_handler,
                              AuraHelper $aura_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
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
      $container->get('config.factory'),
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
      '#theme' => 'aura_rewards_header',
      '#learn_more_link' => $this->configFactory->get('alshaya_aura_react.settings')->get('aura_rewards_header_learn_more_link'),
      '#strings' => _alshaya_aura_static_strings(),
      '#attached' => [
        'library' => [
          'alshaya_white_label/aura-loyalty-forms',
        ],
      ],
      '#cache' => [
        'contexts' => [
          'user.roles:anonymous',
        ],
        'tags' => [
          'config:alshaya_aura_react.settings',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Show block only if aura is enabled.
    return AccessResult::allowedIf($this->auraHelper->isAuraEnabled());
  }

}
