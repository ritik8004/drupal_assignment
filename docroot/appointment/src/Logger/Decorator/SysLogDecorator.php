<?php

namespace App\Logger\Decorator;

use App\Logger\Formatter\MiddlewareLogFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

/**
 * SysLog handler decorator.
 */
class SysLogDecorator extends SyslogHandler {

  /**
   * Log formatter.
   *
   * @var \App\Logger\Formatter\MiddlewareLogFormatter
   */
  protected $middleWareLogFormatter;

  /**
   * SysLogDecorator constructor.
   *
   * @param \App\Logger\Formatter\MiddlewareLogFormatter $middleware_log_formatter
   *   Log formatter.
   */
  public function __construct(MiddlewareLogFormatter $middleware_log_formatter) {
    $this->middleWareLogFormatter = $middleware_log_formatter;
    $identity = $_SERVER['AH_SITE_NAME'] ?? 'drupal';
    $logger = Logger::DEBUG;
    // For production, we don;t use debug level.
    if (isset($_ENV['AH_SITE_ENVIRONMENT'])
      && preg_match('/\d{2}(live|update)/', $_ENV['AH_SITE_ENVIRONMENT'])) {
      $logger = Logger::INFO;
    }
    parent::__construct($identity, LOG_LOCAL0, $logger);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultFormatter() {
    return $this->middleWareLogFormatter;
  }

}
