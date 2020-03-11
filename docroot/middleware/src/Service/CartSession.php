<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class CartSession.
 */
class CartSession {

  /**
   * The cart storage key.
   */
  const STORAGE_KEY = 'acq_cart_middleware';

  /**
   * Current cart session info.
   *
   * @var array
   */
  protected $sessionCartInfo = [];

  /**
   * CartSession constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Service for session.
   */
  public function __construct(SessionInterface $session) {
    $this->session = $session;
  }

  /**
   * Return the given key value from session.
   *
   * @param string $key
   *   The key to find in current session.
   *
   * @return mixed|null
   *   Return value of given key or null.
   */
  protected function getSessionItem(string $key) {
    $this->loadCartFromSession();
    return !empty($this->sessionCartInfo[$key])
      ? $this->sessionCartInfo[$key]
      : NULL;
  }

  /**
   * Start session and set cart data.
   *
   * @param bool $force
   *   TRUE to load cart info from session forcefully, false otherwise.
   *
   * @return array|mixed
   *   Return array of cart session info.
   */
  public function loadCartFromSession($force = FALSE) {
    if (!$this->session->isStarted()) {
      $this->session->start();
    }

    if (empty($this->sessionCartInfo) || $force) {
      $this->sessionCartInfo = $this->session->get(self::STORAGE_KEY);
    }
    return $this->sessionCartInfo;
  }

  /**
   * Update cart id to middleware session.
   *
   * @param int $cart_id
   *   The cart id.
   * @param int|null $customer_id
   *   (Optional) the customer id.
   * @param string|null $email
   *   (Optional) the customer email.
   */
  public function updateCartSession(int $cart_id, int $customer_id = NULL, $email = NULL) {
    if (empty($this->sessionCartInfo)) {
      $this->loadCartFromSession();
    }

    $update = FALSE;
    if (empty($this->sessionCartInfo['cart_id'])) {
      $update = TRUE;
      $this->sessionCartInfo['cart_id'] = $cart_id;
    }

    if (!empty($customer_id)) {
      $update = TRUE;
      $this->sessionCartInfo['customer_id'] = (int) $customer_id;
    }

    if (!empty($email)) {
      $update = TRUE;
      $this->sessionCartInfo['customer_email'] = $email;
    }

    if ($update) {
      $this->setSession($this->sessionCartInfo);
    }
  }

  /**
   * Return user id from current session.
   *
   * @return int|null
   *   Return user id or null.
   */
  public function getSessionUid() {
    if (!empty($this->sessionCartInfo['uid'])) {
      return $this->sessionCartInfo['uid'];
    }

    return $this->getSessionItem('uid');
  }

  /**
   * Return customer id from current session.
   *
   * @return int|null
   *   Return customer id or null.
   */
  public function getSessionCustomerId() {
    if (!empty($this->sessionCartInfo['customer_id'])) {
      return $this->sessionCartInfo['customer_id'];
    }

    return $this->getSessionItem('customer_id');
  }

  /**
   * Set session data.
   *
   * @param array $sessionData
   *   The array of data to store.
   *
   * @return array|mixed
   *   Return the array of stored data from sesssion.
   */
  public function setSession(array $sessionData) {
    $this->session->set(self::STORAGE_KEY, $sessionData);
    return $this->loadCartFromSession(TRUE);
  }

  /**
   * Clear session data.
   *
   * @return array
   *   Return empty array.
   */
  public function clearSession() {
    $this->session->remove(self::STORAGE_KEY);
    $this->sessionCartInfo = [];
    return [];
  }

}
