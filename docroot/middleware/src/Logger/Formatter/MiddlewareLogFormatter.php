<?php

namespace App\Logger\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * Class MiddlewareLogFormatter.
 */
class MiddlewareLogFormatter implements FormatterInterface {

  /**
   * Logger format similar to Drupal.
   */
  const LOGGER_FORMAT = '!base_url|!timestamp|!type|!ip|!request_uri|!referer|!uid|!link|!message';

  /**
   * {@inheritdoc}
   */
  public function format(array $record) {
    return strtr(self::LOGGER_FORMAT, [
      '!base_url' => $record['extra'] ? $record['extra']['server'] : '',
      '!timestamp' => time(),
      '!type' => $record['channel'],
      '!ip' => $record['extra'] ? $record['extra']['ip'] : '',
      '!request_uri' => $record['extra'] ? $record['extra']['url'] : '',
      '!referer' => $record['extra'] ? $record['extra']['referrer'] : '',
      '!severity' => $record['level'],
      '!uid' => 0,
      '!link' => '',
      '!message' => strip_tags($record['message']),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function formatBatch(array $records) {
    foreach ($records as $key => $record) {
      $records[$key] = $this->format($record);
    }

    return $records;
  }

}
