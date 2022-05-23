<?php

namespace Drupal\alshaya_hello_member\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_hello_member\Helper\HelloMemberHelper;

/**
 * MyAccountsPointsHistoryController for hello membter points history page.
 *
 * @package Drupal\alshaya_hello_member\Controller
 */
class MyAccountsPointsHistoryController extends ControllerBase {

  /**
   * Hello Member Helper service object.
   *
   * @var Drupal\alshaya_hello_member\Helper\HelloMemberHelper
   */
  protected $helloMemberHelper;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * MyAccountsPointsHistoryController constructor.
   *
   * @param Drupal\alshaya_hello_member\Helper\HelloMemberHelper $hello_member_helper
   *   The hello member helper service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(HelloMemberHelper $hello_member_helper,
                              ModuleHandlerInterface $module_handler) {
    $this->helloMemberHelper = $hello_member_helper;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_hello_member.hello_member_helper'),
      $container->get('module_handler'),
    );
  }

  /**
   * View hello member points history.
   */
  public function pointsHistory() {
    $this->moduleHandler->loadInclude('alshaya_hello_member', 'inc', 'alshaya_hello_member.static_strings');

    return [
      '#theme' => 'my_accounts_points_history',
      '#strings' => _alshaya_hello_member_static_strings(),
      '#attached' => [
        'library' => [
          'alshaya_hello_member/alshaya_hello_member_my_accounts_points_history',
        ],
      ],
    ];
  }

  /**
   * Helper method to check access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function checkAccess() {
    return AccessResult::allowedIf($this->helloMemberHelper->isHelloMemberEnabled());
  }

}
