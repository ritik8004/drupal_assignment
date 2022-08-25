<?php

namespace Drupal\alshaya_hello_member\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\alshaya_hello_member\Helper\HelloMemberHelper;

/**
 * Provides My Accounts Hello Member block.
 *
 * @Block(
 *   id = "become_hello_member_block",
 *   admin_label = @Translation("Become Hello Member block")
 * )
 */
class BecomeHelloMemberBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Hello Member Helper service object.
   *
   * @var Drupal\alshaya_hello_member\Helper\HelloMemberHelper
   */
  protected $helloMemberHelper;

  /**
   * HelloMemberBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\alshaya_hello_member\Helper\HelloMemberHelper $hello_member_helper
   *   The Hello Member service.
   */
  public function __construct(array $configuration,
                                    $plugin_id,
                                    $plugin_definition,
                              HelloMemberHelper $hello_member_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('alshaya_hello_member.hello_member_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#attached' => [
        'library' => [
          'alshaya_hello_member/alshaya_hello_member_become_hello_member',
        ],
      ],
      '#markup' => '<div id="hello-member-become-hello-member-block"></div>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Show block only for guest users if hello member is enabled.
    return AccessResult::allowedIf(
      $account->isAnonymous() && $this->helloMemberHelper->isHelloMemberEnabled()
    );
  }

}
