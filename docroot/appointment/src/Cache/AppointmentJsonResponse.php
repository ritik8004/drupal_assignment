<?php

namespace App\Cache;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Appointment Json Response.
 *
 * @package App\Cache
 */
class AppointmentJsonResponse extends JsonResponse {

  /**
   * AppointmentJsonResponse Constructor.
   *
   * @param mixed $data
   *   The response data.
   * @param bool $cache
   *   Response is cached or no-cache.
   * @param int $status
   *   The response status code.
   * @param array $headers
   *   An array of response headers.
   * @param bool $json
   *   If the data is already a JSON string.
   */
  public function __construct($data = NULL, bool $cache = FALSE, int $status = 200, array $headers = [], bool $json = FALSE) {
    parent::__construct($data, 200, $headers, FALSE);

    if ($cache) {
      $this->setMaxAge($_ENV['APPOINTMENT_API_RESPONSE_MAX_AGE']);
    }
    else {
      $this->setMaxAge(0);
      $this->headers->set('cache-control', 'must-revalidate, no-cache, no-store, private');
    }
  }

}
