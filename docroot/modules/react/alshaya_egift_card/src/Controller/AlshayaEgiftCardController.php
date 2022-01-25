<?php

namespace Drupal\alshaya_egift_card\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\alshaya_egift_card\Helper\EgiftCardHelper;
use Drupal\Core\Session\AccountInterface;

/**
 * Alshaya Egift Cards Controller.
 */
class AlshayaEgiftCardController extends ControllerBase {
  /**
   * Egiftcard Helper service object.
   *
   * @var \Drupal\alshaya_egift_card\Helper\EgiftCardHelper
   */
  protected $egiftCardHelper;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * AlshayaEgiftCardController constructor.
   *
   * @param \Drupal\alshaya_egift_card\Helper\EgiftCardHelper $egiftCardHelper
   *   The egift card helper service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    EgiftCardHelper $egiftCardHelper,
    AccountInterface $current_user
  ) {
    $this->egiftCardHelper = $egiftCardHelper;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_egift_card.egift_card_helper'),
      $container->get('current_user'),
    );
  }

  /**
   * View My Egift Card Page.
   */
  public function getLinkedCardPage() {
    $config = $this->config('alshaya_egift_card.settings');

    return [
      '#type' => 'markup',
      '#markup' => '<div id="my-egift-card"></div>',
      '#attached' => [
        'library' => [
          'alshaya_egift_card/alshaya_egift_card_my_account',
          'alshaya_white_label/egift-myaccount',
        ],
      ],
      '#cache' => [
        'tags' => Cache::mergeTags([], $config->getCacheTags()),
      ]
    ];
  }

  /**
   * Helper method to check access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function checkAccess() {
    // Only logged-in users will be able to access my eGift card page if enabled.
    return AccessResult::allowedIf($this->egiftCardHelper->isEgiftCardEnabled() && $this->currentUser->isAuthenticated());
  }

  /**
   * Egift card purchase page.
   *
   * @return array
   *   Markup for eGift card purchase react app.
   */
  public function eGiftCardPurchase():array {
    $config = $this->config('alshaya_egift_card.settings');

    $build = [
      '#type' => 'markup',
      '#markup' => '<div id="egift-card-purchase-wrapper"></div>',
      '#attached' => [
        'library' => [
          'alshaya_egift_card/alshaya_egift_card_purchase',
          'alshaya_acm_cart_notification/cart_notification_js',
          'alshaya_white_label/egift-purchase-page',
        ],
        'drupalSettings' => [
          'egiftCard' => [
            'textAreaMaxlength' => $config->get('textarea_maxlength')
          ]
        ],
      ],
    ];

    $build['#cache']['tags'] = Cache::mergeTags([], $config->getCacheTags());

    return $build;
  }

  function getUserEgiftPageTitle() {
    return $this->t('eGift Card', [], ['context' => 'egift']);
  }

}
