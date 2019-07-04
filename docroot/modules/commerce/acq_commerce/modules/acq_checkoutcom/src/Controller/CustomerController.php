<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomerController.
 *
 * @package Drupal\acq_checkoutcom\Controller
 */
class CustomerController extends ControllerBase {

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
   * CustomerController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer service object.
   * @param \Drupal\acq_checkoutcom\ApiHelper $api_helper
   *   The api helper object.
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   The current user.
   */
  public function __construct(
    Request $current_request,
    Renderer $renderer,
    ApiHelper $api_helper,
    AccountProxyInterface $account_proxy
  ) {
    $this->currentRequest = $current_request;
    $this->renderer = $renderer;
    $this->apiHelper = $api_helper;
    $this->currentUser = $account_proxy;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('renderer'),
      $container->get('acq_checkoutcom.agent_api'),
      $container->get('current_user')
    );
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
    return AccessResult::allowedIf(
      !empty($user->get('acq_customer_id')->getString())
      && $this->currentUser->id() == $user->id()
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
    $options = [];
    if ($existing_cards = $this->apiHelper->getCustomerCards($user)) {
      foreach ($existing_cards as $card) {
        $options[$card['id']] = [
          '#theme' => 'payment_card_info',
          '#card_info' => $card,
          '#user' => $user,
        ];
      }
      $list_class = ['saved-paymentcard-list'];
    }
    else {
      $options['empty'] = [
        '#markup' => $this->t('You dont have any saved payment cards.'),
      ];
      $list_class = ['saved-paymentcard-list', 'empty'];
    }

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $options,
      '#attributes' => [
        'class' => $list_class,
      ],
      '#cache' => [
        'tags' => [
          'user:' . $this->currentUser->id() . ':payment_cards',
        ],
      ],
    ];
  }

  /**
   * Remove card for given user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param string $card_id
   *   The card id to delete.
   *
   * @return array
   *   Return the build array.
   */
  public function removeCard(UserInterface $user, string $card_id) {
    return $this->formBuilder()->getForm('\Drupal\acq_checkoutcom\Form\CustomerCardDeleteForm', $user, $card_id);
  }

}
