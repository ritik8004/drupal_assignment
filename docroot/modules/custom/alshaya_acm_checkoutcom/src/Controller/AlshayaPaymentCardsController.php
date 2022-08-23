<?php

namespace Drupal\alshaya_acm_checkoutcom\Controller;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\acq_checkoutcom\Controller\PaymentCardsController;
use Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for Payment Cards.
 *
 * @package Drupal\alshaya_acm_checkoutcom\Controller
 */
class AlshayaPaymentCardsController extends PaymentCardsController {

  /**
   * Alshaya Checkout.com API Helper.
   *
   * @var \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper
   */
  protected $checkoutComHelper;

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
   * @param \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper $checkout_com_helper
   *   Alshaya Checkout.com API Helper.
   */
  public function __construct(Request $current_request,
                              Renderer $renderer,
                              ApiHelper $api_helper,
                              AccountProxyInterface $account_proxy,
                              MessengerInterface $messenger,
                              AlshayaAcmCheckoutComAPIHelper $checkout_com_helper) {
    parent::__construct($current_request, $renderer, $api_helper, $account_proxy, $messenger);
    $this->checkoutComHelper = $checkout_com_helper;
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
      $container->get('messenger'),
      $container->get('alshaya_acm_checkoutcom.api_helper')
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
    $config = $this->config('alshaya_user.settings');
    $enabled_links = $config->get('my_account_enabled_links');
    // phpcs:ignore
    $enabled_links = unserialize($enabled_links);

    $checkout_config = $this->config('alshaya_acm_checkout.settings');

    $vault_enabled = FALSE;
    if (!empty($enabled_links['payment_cards'])) {
      switch ($this->checkoutComHelper->getCurrentMethod()) {
        case 'checkout_com':
          $vault_enabled = $this->apiHelper->getCheckoutcomConfig('vault_enabled');
          break;

        case 'checkout_com_upapi':
          $config = $this->checkoutComHelper->getCheckoutcomUpApiConfig();
          $vault_enabled = $config['vault_enabled'] ?? FALSE;
          break;

      }
    }

    $access_result = AccessResult::allowedIf(
      ($this->checkoutComHelper->getCustomerId() > 0)
      && $account->id() == $user->id()
      && $vault_enabled
    );

    $access_result->addCacheableDependency($config);
    $access_result->addCacheableDependency($checkout_config);

    return $access_result;
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
    if ($this->checkoutComHelper->getCurrentMethod() === 'checkout_com') {
      return parent::listCards($user);
    }

    $list_class = ['saved-paymentcard-list'];
    $existing_cards = $this->checkoutComHelper->getSavedCards();

    $options = [];
    if (!empty($existing_cards) && is_array($existing_cards)) {
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
    if ($this->checkoutComHelper->getCurrentMethod() === 'checkout_com') {
      return parent::removeCard($user, $public_hash);
    }

    return $this->formBuilder()->getForm('\Drupal\alshaya_acm_checkoutcom\Form\CustomerCardDeleteForm', $user, $public_hash);
  }

}
