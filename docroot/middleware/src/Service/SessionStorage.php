<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SessionStorage.
 *
 * @package App\Service
 */
class SessionStorage {

  /**
   * Service for session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * SessionStorage constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Service for session.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(SessionInterface $session, RequestStack $request_stack) {
    $this->session = $session;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * Get data from session.
   *
   * @param string $key
   *   Key for which we need the value.
   *
   * @return mixed|null
   *   Data from session.
   */
  public function getDataFromSession(string $key) {
    // If session cookie not available or session not started.
    if (!$this->request->cookies->has('PHPSESSID')
      && session_status() == PHP_SESSION_NONE) {
      return NULL;
    }

    return $this->session->get($key);
  }

  /**
   * Update data in session.
   *
   * Starts the session if required.
   *
   * @param string $key
   *   Data key.
   * @param mixed $new_value
   *   (Optional) Value to set.
   */
  public function updateDataInSession(string $key, $new_value = NULL) {
    $value = $this->getDataFromSession($key);

    if ($value !== $new_value) {
      $this->session->set($key, $new_value);
    }
  }

}
