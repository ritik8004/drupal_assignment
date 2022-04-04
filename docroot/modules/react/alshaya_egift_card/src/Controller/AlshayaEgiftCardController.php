<?php

namespace Drupal\alshaya_egift_card\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
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
    if (!$this->egiftCardHelper->isEgiftCardEnabled()) {
      // Redirect to homepage if egift is not enabled.
      $response = new LocalRedirectResponse(Url::fromRoute('<front>')->toString());
      $response->send();
    }

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
          ],
          'addToCartNotificationTime' => $this->config('alshaya_acm_cart_notification.settings')->get('notification_time'),
        ],
      ],
    ];

    $build['#cache']['tags'] = Cache::mergeTags(['alshaya_acm_cart_notification.settings'], $config->getCacheTags());

    return $build;
  }

  function getUserEgiftPageTitle() {
    return $this->t('eGift Card', [], ['context' => 'egift']);
  }

  /**
   * Egift card purchase page title.
   */
  function egiftPageTitle() {
    return $this->t('Buy eGift Card', [], ['context' => 'egift']);
  }

  /**
   * Redirects the user to my-account e-gift page if logged-in.
   * Redirects the user to login page and then my-account e-gift page after login if user is anonymous.
   * If e-Gift feature is disabled, redirect to `/user`.
   */
  function linkCard() {
    if (!$this->egiftCardHelper->isEgiftCardEnabled()) {
      // If egift not enabled then redirect to /user page.
      $url = Url::fromRoute('user.page');
    }
    else if ($this->currentUser()->isAuthenticated()) {
      // If authenticated user then redirect to egift card page in my account.
      $url = Url::fromRoute('alshaya_egift_card.my_egift_card', ['user' => $this->currentUser->id()]);
    }
    else {
      // If anonymous user then redirect to user/login with destination param.
      $url = Url::fromRoute('user.login');
      $destination = Url::fromRoute('alshaya_egift_card.link-egift');
      $url->setOptions(['query' => ['destination' => $destination->toString()]]);
    }

    $response = new LocalRedirectResponse($url->toString());
    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheContexts(['user']);
    $cacheableMetadata->setCacheMaxAge(0);
    $response->addCacheableDependency($cacheableMetadata);
    $response->send();
  }

}
