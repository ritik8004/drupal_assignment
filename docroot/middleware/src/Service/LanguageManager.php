<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Language Manager class to set/know if user changed language.
 *
 * @package App\Service
 */
class LanguageManager {

  /**
   * Session Storage.
   *
   * @var \App\Service\SessionStorage
   */
  protected $storage;

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * SessionCache constructor.
   *
   * @param \App\Service\SessionStorage $storage
   *   Session Storage.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   RequestStack Object.
   */
  public function __construct(SessionStorage $storage,
                              RequestStack $requestStack) {
    $this->storage = $storage;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * Check if used changed language.
   *
   * @return bool
   *   TRUE if language changed.
   */
  public function isLanguageChanged() {
    $current_language = $this->request->query->get('lang', 'en');
    $last_known_language = $this->storage->getDataFromSession('language');

    if (empty($last_known_language)) {
      $this->storage->updateDataInSession('language', $current_language);
      return FALSE;
    }

    if ($last_known_language !== $current_language) {
      $this->storage->updateDataInSession('language', $current_language);
      return TRUE;
    }

    return FALSE;
  }

}
