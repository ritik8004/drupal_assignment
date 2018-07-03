<?php

namespace Drupal\acq_commerce\Connector;

/**
 * Class CustomerNotFoundException.
 *
 * @package Drupal\acq_commerce\Connector
 *
 * @ingroup acq_commerce
 */
class CustomerNotFoundException extends ConnectorException {

  const CUSTOMER_NOT_FOUND_CODE = 102;
  const CUSTOMER_NOT_FOUND_MESSAGE = "Customer not found";

}

