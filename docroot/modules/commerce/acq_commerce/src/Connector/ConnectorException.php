<?php

namespace Drupal\acq_commerce\Connector;

/**
 * Class Connector Exception.
 *
 * @package Drupal\acq_commerce\Connector
 *
 * @ingroup acq_commerce
 */
class ConnectorException extends \UnexpectedValueException {

  /**
   * {@inheritdoc}
   */
  public function __construct($message = '', $code = 0, \Throwable $e = NULL) {
    $message_overridden = FALSE;
    $original_message = $message;
    // If error contains any HTML, or contains 'magento' string, use global
    // error message.
    if ($message != strip_tags($message)
      || (stripos($message, 'magento') > -1)) {
      $message = acq_commerce_api_down_global_error_message();
      $message_overridden = TRUE;
    }
    elseif (($position = stripos($message, 'Backend server error:')) > -1) {
      $prefix = 'Backend server error:';
      $message = trim(substr($message, $position + strlen($prefix)));
      $message_overridden = TRUE;
    }

    // Log the message if changed.
    if ($message_overridden) {
      \Drupal::logger('acq_commerce')->error($original_message);
    }

    return parent::__construct($message, $code, $e);
  }

}
