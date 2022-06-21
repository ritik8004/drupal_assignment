<?php

namespace Drupal\alshaya_hello_member\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_hello_member\Helper\HelloMemberHelper;
use Drupal\user\UserInterface;
use Drupal\Core\Session\AccountProxyInterface;

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
   * The current account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * MyAccountsPointsHistoryController constructor.
   *
   * @param Drupal\alshaya_hello_member\Helper\HelloMemberHelper $hello_member_helper
   *   The hello member helper service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_account
   *   The current account object.
   */
  public function __construct(HelloMemberHelper $hello_member_helper,
                              ModuleHandlerInterface $module_handler,
                              AccountProxyInterface $current_account) {
    $this->helloMemberHelper = $hello_member_helper;
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_hello_member.hello_member_helper'),
      $container->get('module_handler'),
      $container->get('current_user'),
    );
  }

  /**
   * View hello member points history.
   */
  public function pointsHistory() {
    $this->moduleHandler->loadInclude('alshaya_hello_member', 'inc', 'alshaya_hello_member.static_strings');

    // Get config for hello member page.
    $helloMemberConfig = $this->config('alshaya_hello_member.settings');

    return [
      '#theme' => 'my_accounts_points_history',
      '#strings' => _alshaya_hello_member_static_strings(),
      '#attached' => [
        'library' => [
          'alshaya_hello_member/alshaya_hello_member_my_accounts_points_history',
          'alshaya_white_label/hello-member-points-history',
        ],
        'drupalSettings' => [
          'pointsHistoryPageSize' => $helloMemberConfig->get('points_history_page_size'),
        ],
      ],
      '#cache' => [
        'tags' => $helloMemberConfig->getCacheTags(),
      ],
    ];
  }

  /**
   * Helper method to check access.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function checkAccess(UserInterface $user) {
    if (empty($user)) {
      return AccessResult::forbidden();
    }

    if ($user->id() === 0 || $user->id() !== $this->currentUser->id()) {
      return AccessResult::forbidden();
    }

    // Check if my account reviews config enabled.
    $settings['enabled'] = $this->helloMemberHelper->isHelloMemberEnabled();
    if ($settings['enabled']) {
      return AccessResult::allowedIf($settings['enabled'])->addCacheTags(['config:' . $settings['enabled'] . '.settings']);
    }

    return AccessResult::allowed();
  }

  /**
   * Returns a page title.
   */
  public function getTitle() {
    return $this->t('Points History', [], ['context' => 'hello_member']);
  }

}
