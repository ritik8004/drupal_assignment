<?php

namespace Drupal\alshaya_performance\Logger;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\syslog\Logger\SysLog;

/**
 * Redirects logging messages to syslog.
 */
class AlshayaPerformanceSysLog extends SysLog {

  const ALSHAYA_PERFORMANCE_PRODUCTION_MODE = 'production';

  /**
   * A configuration object containing custom alshaya_performance settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $alshayaSettings;

  /**
   * Constructs a SysLog object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory object.
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LogMessageParserInterface $parser) {
    $this->alshayaSettings = $config_factory->get('alshaya_performance.settings');
    parent::__construct($config_factory, $parser);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    // Don't log debug messages on production env.
    if ($level == RfcLogLevel::DEBUG
      && $this->alshayaSettings->get('mode') === self::ALSHAYA_PERFORMANCE_PRODUCTION_MODE) {
      return;
    }

    parent::log($level, $message, $context);
  }

}
