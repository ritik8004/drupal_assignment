<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AlshayaJsonResponse.
 *
 * @package App\Response
 */
class AlshayaJsonResponse extends JsonResponse {

  /**
   * {@inheritdoc}
   */
  public function __construct($data = NULL,
                              int $status = 200,
                              array $headers = [],
                              bool $json = FALSE) {
    parent::__construct($data, $status, $headers, $json);

    // Disable cache for all the requests.
    $this->setMaxAge(0);
  }

}
