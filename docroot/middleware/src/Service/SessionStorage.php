<?php

namespace App\Service;

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
   * SessionStorage constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Service for session.
   */
  public function __construct(SessionInterface $session) {
    $this->session = $session;
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
