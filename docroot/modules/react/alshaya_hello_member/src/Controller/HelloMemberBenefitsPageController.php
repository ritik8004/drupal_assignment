<?php

namespace Drupal\alshaya_hello_member\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_hello_member\Helper\HelloMemberHelper;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * HelloMemberBenefitsPageController for hello membter points history page.
 *
 * @package Drupal\alshaya_hello_member\Controller
 */
class HelloMemberBenefitsPageController extends ControllerBase {

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
   * HelloMemberBenefitsPageController constructor.
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
   * View details per benefits.
   *
   * @param string $type
   *   Offer/Coupon type.
   * @param string $code
   *   Offer/Coupon code.
   */
  public function getBenefitsDetails(string $type, string $code) {
    $this->moduleHandler->loadInclude('alshaya_hello_member', 'inc', 'alshaya_hello_member.static_strings');

    return [
      '#theme' => 'hello_member_benefits_page',
      '#strings' => _alshaya_hello_member_static_strings(),
      '#attached' => [
        'library' => [
          'alshaya_hello_member/alshaya_hello_member_benefits_page',
        ],
        'drupalSettings' => [
          'helloMemberBenefits' => [
            'type' => $type,
            'code' => $code,
          ],
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
  public function checkAccess(AccountInterface $account, UserInterface $user) {
    // Only logged in users will be able to access this page.
    if (empty($user) || $account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    // If current user is the one for which this page is requested.
    if (!($account->id() == $user->id())) {
      return AccessResult::forbidden();
    }

    $settings['enabled'] = $this->helloMemberHelper->isHelloMemberEnabled();
    return AccessResult::allowedIf($settings['enabled'])->addCacheTags(['config:' . $settings['enabled'] . '.settings']);
  }

  /**
   * Returns a page title.
   */
  public function getTitle() {
    return $this->t('Benefits', [], ['context' => 'hello_member']);
  }

}
