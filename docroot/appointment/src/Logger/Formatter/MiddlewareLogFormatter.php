<?php

namespace App\Logger\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * Helper class for formatting Middleware Logs.
 */
class MiddlewareLogFormatter implements FormatterInterface {

  /**
   * Logger format similar to Drupal.
   */
  public const LOGGER_FORMAT = '!base_url|!timestamp|!type|!ip|!request_uri|!referer|!uid|!link|!message';

  /**
   * {@inheritdoc}
   */
  public function format(array $record) {
    // Get and handle placeholders.
    $message_placeholders = $this->getMessagePlaceholder($record['context']);
    $record['message'] = strtr($record['message'], $message_placeholders);

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

  /**
   * Prepare placeholder variables.
   *
   * @param array $context
   *   Placeholder context array.
   *
   * @return array
   *   Placeholder array.
   */
  private function getMessagePlaceholder(array $context) {
    $variables = [];
    foreach ($context as $key => $variable) {
      if (!empty($key) && is_array($key) && ($key[0] === '@' || $key[0] === '%' || $key[0] === '!')) {
        $variables[$key] = $variable;
      }
    }

    return $variables;
  }

}
