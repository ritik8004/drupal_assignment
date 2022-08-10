<?php

namespace Drupal\acq_sku\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a acq sku validator event for event listeners.
 */
class ProcessBlackListedProductsEvent extends Event {

  public const EVENT_NAME = 'acq_sku.processBlackListedProduct';

}
