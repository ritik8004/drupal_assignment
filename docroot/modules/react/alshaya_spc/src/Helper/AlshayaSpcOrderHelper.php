<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AlshayaSpcCustomerHelper.
 */
class AlshayaSpcOrderHelper {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The spc cookies handler..
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcCookies
   */
  protected $spcCookies;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\Session
   */
  protected $session;

  /**
   * Address book manager.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  protected $addressBookManager;

  /**
   * Orders manager service object.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * The current user making the request.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * AlshayaSpcCustomerHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   The api wrapper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcCookies $spc_cookies
   *   The spc cookies handler.
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   *   The session.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address book manager.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaApiWrapper $api_wrapper,
    ModuleHandlerInterface $module_handler,
    AlshayaSpcCookies $spc_cookies,
    Session $session,
    AlshayaAddressBookManager $address_book_manager,
    OrdersManager $orders_manager,
    AccountProxyInterface $current_user
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->apiWrapper = $api_wrapper;
    $this->moduleHandler = $module_handler;
    $this->spcCookies = $spc_cookies;
    $this->session = $session;
    $this->addressBookManager = $address_book_manager;
    $this->ordersManager = $orders_manager;
    $this->currentUser = $current_user;
  }

  /**
   * Helper function to return order from session.
   *
   * @return array
   *   Order array if found.
   */
  public function getLastOrderFromSession($reset = FALSE) {
    static $order;

    if (!empty($order)) {
      return $order;
    }

    $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $order_id = $this->session->get('last_order_id');

    // Throw access denied if nothing in session.
    if (empty($order_id)) {
      throw new AccessDeniedHttpException();
    }

    if ($this->currentUser->isAnonymous() || !alshaya_acm_customer_is_customer($this->currentUser)) {
      $email = $this->session->get('email_order_' . $order_id);
    }
    else {
      $email = $this->currentUser->getEmail();
    }

    // If flag is set to reset cache.
    if ($reset) {
      $this->ordersManager->clearOrderCache($email);
    }

    $orders = alshaya_acm_customer_get_user_orders($email);
    $order_index = array_search($order_id, array_column($orders, 'order_id'));

    if ($order_index === FALSE) {
      // If we don't find the order in first go, clear cache and search.
      if (!$reset) {
        $this->getLastOrderFromSession(TRUE);
      }

      // We didn't find even after clearing cache. Throw error now.
      throw new NotFoundHttpException();
    }

    $order = $orders[$order_index];

    return $order;
  }

}
