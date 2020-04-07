<?php

namespace App\Logger\Decorator;

use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

/**
 * SysLog handler decorator.
 */
class SysLogDecorator extends SyslogHandler {

  /**
   * SysLogDecorator constructor.
   */
  public function __construct() {
    $identity = isset($_SERVER['AH_SITE_NAME']) ? $_SERVER['AH_SITE_NAME'] : 'drupal';
    $logger = Logger::DEBUG;
    // For production, we don;t use debug level.
    if (isset($_ENV['AH_SITE_ENVIRONMENT'])
      && preg_match('/\d{2}(live|update)/', $_ENV['AH_SITE_ENVIRONMENT'])) {
      $logger = Logger::INFO;
    }
    parent::__construct($identity, LOG_LOCAL0, $logger);
  }

}
