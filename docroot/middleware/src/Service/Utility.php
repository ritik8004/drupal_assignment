<?php

namespace App\Service;

/**
 * Class Utility.
 *
 * This will never rely on any other service.
 * This is intended to provide some basic utility functions for re-usable code.
 */
class Utility {

  /**
   * Get default error message.
   *
   * @return string
   *   Default error message.
   */
  public function getDefaultErrorMessage() {
    // @TODO: t().
    return 'Sorry, something went wrong. Please try again later.';
  }

  /**
   * Method for error response.
   *
   * @param string $message
   *   Error message.
   * @param string $code
   *   Error code.
   *
   * @return array
   *   Error response array.
   */
  public function getErrorResponse(string $message, string $code) {
    return [
      'error' => TRUE,
      'error_message' => $message,
      'error_code' => $code,
    ];
  }

}
