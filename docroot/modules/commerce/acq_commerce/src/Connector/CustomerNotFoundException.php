<?php

namespace Drupal\acq_commerce\Connector;

/**
 * Class Customer Not Found Exception.
 *
 * @package Drupal\acq_commerce\Connector
 *
 * @ingroup acq_commerce
 */
class CustomerNotFoundException extends ConnectorException {

  public const CUSTOMER_NOT_FOUND_CODE = 102;
  public const CUSTOMER_NOT_FOUND_MESSAGE = "Customer not found";

}
