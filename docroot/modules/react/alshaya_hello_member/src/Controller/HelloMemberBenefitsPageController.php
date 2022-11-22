<?php

namespace Drupal\alshaya_hello_member\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;
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
   * HelloMemberBenefitsPageController constructor.
   *
   * @param Drupal\alshaya_hello_member\Helper\HelloMemberHelper $hello_member_helper
   *   The hello member helper service.
   */
  public function __construct(HelloMemberHelper $hello_member_helper) {
    $this->helloMemberHelper = $hello_member_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_hello_member.hello_member_helper'),
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
    $this->moduleHandler()->loadInclude('alshaya_hello_member', 'inc', 'alshaya_hello_member.static_strings');

    return [
      '#theme' => 'hello_member_benefits_page',
      '#strings' => _alshaya_hello_member_static_strings(),
      '#attached' => [
        'library' => [
          'alshaya_hello_member/alshaya_hello_member_benefits_page',
          'alshaya_white_label/hello-member-benefit-landing-page',
          'alshaya_white_label/hello-member-qr-code',
          'alshaya_seo_transac/gtm_hello_member',
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

    // Check if hello member feature is not enabled.
    if (!($this->helloMemberHelper->isHelloMemberEnabled())) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * Returns a page title.
   */
  public function getTitle() {
    return $this->t('Benefits', [], ['context' => 'hello_member']);
  }

}
