<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AlshayaRedirectResponse.
 *
 * @package App\Response
 */
class AlshayaRedirectResponse extends RedirectResponse {

  /**
   * {@inheritdoc}
   */
  public function __construct(?string $url,
                              int $status = 302,
                              array $headers = []) {
    parent::__construct($url, $status, $headers);

    // Disable cache for all the requests.
    $this->setMaxAge(0);
  }

}
