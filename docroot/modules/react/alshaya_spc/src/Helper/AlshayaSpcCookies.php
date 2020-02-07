<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AlshayaSpcCookies.
 *
 * @package Drupal\alshaya_spc\Helper
 */
class AlshayaSpcCookies {

  // Middleware session key to get from db.
  const MIDDLEWARE_SESSION_KEY = 'acq_cart_middleware';

  // Session cookie key to check.
  const MIDDLEWARE_COOKIE_KEY = 'PHPSESSID';

  /**
   * Array to store all cookies.
   *
   * @var array
   */
  protected $cookies = [];

  /**
   * Array of session data.
   *
   * @var array
   */
  protected $spcSession = [];

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The request service.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * AlshayaSpcCookies constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request object.
   */
  public function __construct(
    Connection $database,
    RequestStack $requestStack
  ) {
    $this->connection = $database;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * Helper method to get session data from table for middleware cookie.
   *
   * @param bool $force
   *   True to fetch from database, otherwise false.
   *
   * @return array|null
   *   Return array of data or null.
   */
  protected function getMiddleWareSessionFromCookie($force = FALSE) {
    if (!empty($this->spcSession) && !$force) {
      return $this->spcSession;
    }

    $this->cookies = $this->request->cookies->all();
    if (empty($this->cookies[self::MIDDLEWARE_COOKIE_KEY])) {
      return NULL;
    }

    $query = $this->connection->select('sessions')
      ->fields('sessions')
      ->condition('sid', Crypt::hashBase64($this->cookies[self::MIDDLEWARE_COOKIE_KEY]));
    $result = $query->execute()->fetchAssoc();

    if (empty($result)) {
      return NULL;
    }

    $this->spcSession = $result['session'];
    return $this->spcSession;
  }

  /**
   * Return the array of middleware session.
   *
   * @return array|null
   *   Return array of session data or null.
   */
  public function getMiddleWareSession() {
    return $this->getMiddleWareSessionFromCookie();
  }

  /**
   * Get the cart id from middleware session.
   *
   * @param bool $force
   *   True to fetch from database, otherwise false.
   *
   * @return string|null
   *   Return the array which contains the cart id.
   */
  public function getSessionCartId($force = FALSE) {
    if ($force) {
      $this->getMiddleWareSessionFromCookie($force);
    }

    $cart_id = &drupal_static(__METHOD__, NULL);
    if (!empty($cart_id)) {
      return $cart_id;
    }

    if (empty($this->spcSession)) {
      return NULL;
    }

    // Get the middleware session key from the record.
    $session_data = array_map(function ($data) {
      return @unserialize($data);
    }, explode('|', $this->spcSession));

    foreach ($session_data as $session_item) {
      if (isset($session_item[self::MIDDLEWARE_SESSION_KEY])) {
        $session_data = $session_item[self::MIDDLEWARE_SESSION_KEY];
        break;
      }
    }

    $cart_id = $session_data['cart_id'] ?? NULL;
    return $cart_id;
  }

  /**
   * Update cart id to middleware session.
   *
   * @param string $cart_id
   *   Cart id to be updated.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|void|null
   *   Return updated id or null.
   */
  public function setSessionCartId($cart_id) {
    if (empty($this->spcSession)) {
      return NULL;
    }

    $prepare_session = [];
    foreach (explode('|', $this->spcSession) as $session_data) {
      $unserialized = @unserialize($session_data);
      if ($unserialized) {
        foreach ($unserialized as &$session_item) {
          if (isset($session_item[self::MIDDLEWARE_SESSION_KEY])) {
            $session_item[self::MIDDLEWARE_SESSION_KEY]['cart_id'] = $cart_id;
          }
        }
        $prepare_session[] = serialize($unserialized);
      }
      else {
        $prepare_session[] = $session_data;
      }
    }

    $session = implode('|', $prepare_session);
    return $this->updateMiddleWareSession($session);
  }

  /**
   * Update new data to middleware session.
   *
   * @param string $session
   *   Processed string to update the middleware session.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   Return updated id or null.
   */
  protected function updateMiddleWareSession(string $session) {
    $query = $this->connection->update('sessions');
    $query->fields(['session' => $session])
      ->condition('sid', Crypt::hashBase64($this->cookies[self::MIDDLEWARE_COOKIE_KEY]));
    return $query->execute();
  }

}
