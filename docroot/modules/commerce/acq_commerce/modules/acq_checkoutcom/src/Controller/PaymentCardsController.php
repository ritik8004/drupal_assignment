<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Payment Cards Controller.
 *
 * @package Drupal\acq_checkoutcom\Controller
 */
class PaymentCardsController extends ControllerBase {

  /**
   * Renderer service object.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * The current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * PaymentCardsController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer service object.
   * @param \Drupal\acq_checkoutcom\ApiHelper $api_helper
   *   The api helper object.
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(
    Request $current_request,
    Renderer $renderer,
    ApiHelper $api_helper,
    AccountProxyInterface $account_proxy,
    MessengerInterface $messenger
  ) {
    $this->currentRequest = $current_request;
    $this->renderer = $renderer;
    $this->apiHelper = $api_helper;
    $this->currentUser = $account_proxy;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('renderer'),
      $container->get('acq_checkoutcom.agent_api'),
      $container->get('current_user'),
      $container->get('messenger')
    );
  }

  /**
   * Helper method to check access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account object.
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function checkAccess(AccountInterface $account, UserInterface $user) {
    return AccessResult::allowedIf(
      !empty($user->get('acq_customer_id')->getString())
      && $account->id() == $user->id()
      && $this->apiHelper->getCheckoutcomConfig('vault_enabled')
    );
  }

  /**
   * Returns the list of saved cards.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders list page is being viewed.
   *
   * @return array
   *   Build array.
   */
  public function listCards(UserInterface $user) {
    $build = [];
    $list_class = ['saved-paymentcard-list'];
    $existing_cards = $this->apiHelper->getCustomerCards($user);

    $options = [];
    if (!empty($existing_cards) && is_string($existing_cards)) {
      $this->messenger->addError($existing_cards);
    }
    elseif (!empty($existing_cards) && is_array($existing_cards)) {
      foreach ($existing_cards as $card) {
        $options[$card['public_hash']] = [
          '#theme' => 'payment_card_info',
          '#card_info' => $card,
          '#user' => $user,
        ];
      }
    }
    else {
      $options['empty'] = [
        '#markup' => $this->t("You don't have any saved payment cards."),
      ];
      $list_class[] = 'empty';
    }

    $build['payment_cards_list'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $options,
      '#attributes' => [
        'class' => $list_class,
      ],
      '#cache' => [
        'tags' => [
          'user:' . $this->currentUser->id(),
        ],
      ],
    ];

    $build['expired_cards_removed'] = [
      '#markup' => '<div class="expired-cards">' . $this->t('Your expired cards will be automatically removed from your account.') . '</div>',
    ];

    return $build;
  }

  /**
   * Remove card for given user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param string $public_hash
   *   The card public hash to delete.
   *
   * @return array
   *   Return the build array.
   */
  public function removeCard(UserInterface $user, string $public_hash) {
    return $this->formBuilder()->getForm('\Drupal\acq_checkoutcom\Form\CustomerCardDeleteForm', $user, $public_hash);
  }

}
