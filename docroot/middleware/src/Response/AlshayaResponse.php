<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class AlshayaResponse.
 *
 * @package App\Response
 */
class AlshayaResponse extends Response {

  /**
   * {@inheritdoc}
   */
  public function __construct($content = '',
                              int $status = 200,
                              array $headers = []) {
    parent::__construct($content, $status, $headers);

    // Disable cache for all the requests.
    $this->setMaxAge(0);
  }

}
