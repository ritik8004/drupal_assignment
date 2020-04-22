<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Class Utility.
 *
 * This will never rely on any other service.
 * This is intended to provide some basic utility functions for re-usable code.
 */
class Utility {

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Utility constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * Get default error message.
   *
   * @return string
   *   Default error message.
   */
  public function getDefaultErrorMessage() {
    return 'Sorry, something went wrong and we are unable to process your request right now. Please try again later.';
  }

  /**
   * Checks if need to return default error message.
   *
   * @param string $message
   *   Error message.
   *
   * @return bool
   *   If message contains MDC server error message.
   */
  public function showDefaultMessage(string $message) {
    $patterns = [
      'report id',
      'curl',
    ];

    $showDefaultMessage = FALSE;
    foreach ($patterns as $pattern) {
      if (stripos($message, $pattern) !== FALSE) {
        $this->logger->error($message);
        $showDefaultMessage = TRUE;
        break;
      }
    }

    return $showDefaultMessage;
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
    if ($this->showDefaultMessage($message)) {
      $message = $this->getDefaultErrorMessage();
    }

    return $message;
  }

}
