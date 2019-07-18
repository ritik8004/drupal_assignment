<?php

namespace Drupal\acq_commerce\Connector;

/**
 * Class ConnectorException.
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
    // If error contains any HTML, or contains 'magento' string, use global
    // error message.
    if ($message != strip_tags($message) || $position = stripos($message, 'magento')) {
      $message = acq_commerce_api_down_global_error_message();
    }
    elseif ($position = stripos($message, 'Backend server error:')) {
      $prefix = 'Backend server error:';
      $message = trim(substr($message, $position + strlen($prefix)));
    }

    return parent::__construct($message, $code, $e);
  }

}
