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
   * Checks if message related with magento log file reporting.
   *
   * @param string $message
   *   Error message.
   *
   * @return bool
   *   If message contains MDC server error message.
   */
  public function isBackendServerError(string $message) {
    // @Todo: Log the original magento error message.
    if (strpos($message, 'Report ID') !== FALSE) {
      return TRUE;
    }

    return FALSE;
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
      'error_message' => $this->processErrorMessage($message),
      'error_code' => $code,
    ];
  }

  /**
   * Process error message.
   *
   * Here error message will be processed so that if we want to change/
   * customize error messages or use as is what we get from magento.
   *
   * @param string $message
   *   Error message.
   *
   * @return string
   *   Error message.
   */
  public function processErrorMessage(string $message) {
    if ($this->isBackendServerError($message)) {
      $message = $this->getDefaultErrorMessage();
    }

    return $message;
  }

}
