<?php

namespace Drupal\google_page_speed\Service;

use Drupal\Component\Datetime\Time;
use Drupal\Core\Database\Connection;

/**
 * Service for database operations.
 *
 * @package Drupal\google_page_speed\Service
 */
class GpsInsightsWrapper {

  /**
   * The Database Connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The DateTime Object.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  protected $time;

  /**
   * GooglePageSpeedCommands constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The Database object to inject database service.
   * @param \Drupal\Component\Datetime\Time $time
   *   Injecting time service.
   */
  public function __construct(Connection $database, Time $time) {
    $this->database = $database;
    $this->time = $time;
  }

  /**
   * To check if url id is present or not.
   *
   * @param string $url
   *   The url to check.
   *
   * @return int
   *   The found url id.
   */
  public function getUrlId($url) {
    $drush_select = $this->database->select('google_page_speed_url', 'gps_url');
    $drush_select->fields('gps_url', ['url_id']);
    $drush_select->condition('url', trim($url));
    $url_id = $drush_select->execute()->fetchField();
    return $url_id;
  }

  /**
   * To insert new url entry.
   *
   * @param string $url
   *   The url to enter.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   The url id of newly created url.
   *
   * @throws \Exception
   */
  public function insertUrlData($url) {
    $url_id = $this->database->insert('google_page_speed_url')
      ->fields(['url'])
      ->values([$url])
      ->execute();
    return $url_id;
  }

  /**
   * To insert new metric.
   *
   * @param int $url_id
   *   The url id.
   * @param string $device
   *   The device type.
   * @param bool $status
   *   The entry status.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   The measure id of newly entered metric.
   *
   * @throws \Exception
   */
  public function insertMeasureData($url_id, $device, $status) {
    $measure_id = $this->database->insert('google_page_speed_measure_attempts')
      ->fields(['url_id', 'device', 'created', 'status'])
      ->values([
        $url_id,
        $device,
        $this->time->getRequestTime(),
        $status,
      ])
      ->execute();
    return $measure_id;
  }

  /**
   * To insert the score based on metric, time and url.
   *
   * @param int $measure_id
   *   The measure id of attempt.
   * @param string $reference
   *   The name of metric.
   * @param float $value
   *   The metric value.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   The id of newly inserted score.
   *
   * @throws \Exception
   */
  public function insertScoreData($measure_id, $reference, $value) {
    $this->database->insert('google_page_speed_measure_data')
      ->fields(['measure_id', 'category', 'reference', 'value'])
      ->values([
        $measure_id,
        'performance',
        $reference,
        $value,
      ])
      ->execute();
    return 1;
  }

  /**
   * To get timestamp based on time.
   *
   * @param string $time
   *   The time to search for.
   *
   * @return \DateTime|false|int
   *   The returning timestamp.
   *
   * @throws \Exception
   */
  public function getTimeStamp($time) {
    switch ($time) {
      case 'one-week':
        $timestamp = strtotime('-1 week');
        break;

      case 'one-month':
        $timestamp = strtotime('-1 month');
        break;

      case 'one-year':
        $timestamp = strtotime('-1 year');
        break;

      case 'three-month':
        $timestamp = strtotime('-3 month');
        break;

      case 'all-time':
        $timestamp = 0;
        break;

      default:
        $timestamp = strtotime('-1 month');
    }

    return $timestamp;
  }

}
