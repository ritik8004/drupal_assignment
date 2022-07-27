<?php

namespace Drupal\alshaya_hello_member\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_hello_member\Helper\HelloMemberHelper;

/**
 * Provides My Accounts Hello Member block.
 *
 * @Block(
 *   id = "become_a_member_block",
 *   admin_label = @Translation("Become a Member block")
 * )
 */
class BecomeMemberBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Hello Member Helper service object.
   *
   * @var Drupal\alshaya_hello_member\Helper\HelloMemberHelper
   */
  protected $helloMemberHelper;

  /**
   * MyAccountsHelloMemberBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param Drupal\alshaya_hello_member\Helper\HelloMemberHelper $hello_member_helper
   *   The Hello Member service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ModuleHandlerInterface $module_handler,
                              HelloMemberHelper $hello_member_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->helloMemberHelper = $hello_member_helper;
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
      $container->get('module_handler'),
      $container->get('alshaya_hello_member.hello_member_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->moduleHandler->loadInclude('alshaya_hello_member', 'inc', 'alshaya_hello_member.static_strings');
    return [
      '#theme' => 'become_a_member_block',
      '#strings' => _alshaya_hello_member_static_strings(),
      '#attached' => [
        'library' => [
          'alshaya_hello_member/alshaya_become_a_member',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Show block only for guest users if hello member is enabled.
    return AccessResult::allowedIf(
      $this->helloMemberHelper->isHelloMemberEnabled() && $account->isAnonymous()
    );
  }

}
