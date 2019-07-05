<?php

namespace Drupal\google_page_speed\Commands;

use Drush\Commands\DrushCommands;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\google_page_speed\Form\GooglePageSpeedConfigForm;
use Drupal\Core\Database\Connection;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Component\Datetime\Time;

/**
 * Class GooglePageSpeedCommands.
 *
 * @package Drupal\google_page_speed\Commands
 */
class GooglePageSpeedCommands extends DrushCommands {
  use StringTranslationTrait;

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * CacheInvalidator object.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheInvalidator;

  /**
   * The Time object.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  protected $time;

  /**
   * GooglePageSpeedCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The ConfigFactory object to inject configfactory service.
   * @param \Drupal\Core\Database\Connection $database
   *   The Database object to inject database service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_invalidator
   *   The CacheInvalidator object to inject cache invalidator service.
   * @param \Drupal\Component\Datetime\Time $time
   *   Injecting time service.
   */
  public function __construct(ConfigFactory $config_factory, Connection $database, CacheTagsInvalidatorInterface $cache_invalidator, Time $time) {
    parent::__construct();
    $this->configFactory = $config_factory;
    $this->database = $database;
    $this->cacheInvalidator = $cache_invalidator;
    $this->time = $time;
  }

  /**
   * Drush command to get insights data.
   *
   * @command google_page_speed:getinsights
   * @aliases gps-gi
   * @options url An option that takes the target url.
   * @options screen An option that takes target screen
   * @usage google_page_speed:insights --url https://google.com --screen desktop
   *   Display data for https://google.com on screen desktop
   *
   * @throws \Exception
   */
  public function insights($options = [
    'url' => '',
    'screen' => '',
  ]) {
    $config = $this->configFactory->get(GooglePageSpeedConfigForm::CONFIG_NAME);
    $api_key = $config->get('api_key');
    if (empty($api_key)) {
      $this->output->writeln('Google API key is not configured.');
    }

    $client = new Client();

    // Get data from options.
    $urls = !empty($options['url'])
      ? (array) $options['url']
      : explode(PHP_EOL, $config->get('page_url'));

    $screens = !empty($options['screen'])
      ? (array) $options['screen']
      : $config->get('screen');

    foreach ($urls as $url) {
      foreach ($screens as $screen) {
        try {
          if (!empty($api_key) && !empty($url) && !empty($screen)) {
            $this->getPageSpeedData($client, $api_key, trim($url), trim($screen));
          }
        }
        catch (RequestException $e) {
          return($this->t('Error'));
        }
      }
    }
    $this->cacheInvalidator->invalidateTags(['google-page-speed:block']);

  }

  /**
   * Gets Insights data and then store it in database against a timestamp.
   *
   * @param \GuzzleHttp\Client $client
   *   The client object to make a request.
   * @param string $api_key
   *   The Google API key for authentication.
   * @param string $url
   *   The url whose data is needed.
   * @param string $screen
   *   The screen for which data is needed.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|int
   *   The integer return statement.
   *
   * @throws \Exception
   *   Throws Exception.
   */
  protected function getPageSpeedData(Client $client, $api_key, $url, $screen) {
    $siteUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?key=' . $api_key . '&url=' . $url . '&strategy=' . $screen;
    $this->output->writeln('Fetching data....');
    $this->output->writeln('URL: ' . $url);
    $this->output->writeln('Screen: ' . $screen);

    $response = $client->get($siteUrl, ['http_errors' => FALSE]);

    // Fetch url_id if present.
    $url_id = $this->getUrlId($url);
    if (empty($url_id)) {
      $url_id = $this->insertUrlData($url);
    }

    if ($response->getStatusCode() == 200) {
      $response_body = $response->getBody();
      $decoded = Json::decode($response_body);
      $categories = $decoded['lighthouseResult']['categories'];
      $audits = $decoded['lighthouseResult']['audits'];

      // Writing data on terminal.
      $this->output->writeln('Performance Score: ' . $categories['performance']['score']);
      $measure_id = $this->insertMeasureData($url_id, $screen, 1);
      $this->insertScoreData($measure_id, 'pf_score', $categories['performance']['score']);
      foreach ($categories['performance']['auditRefs'] as $auditRef) {
        if ($auditRef['weight'] > 0) {
          $this->output->writeln($audits[$auditRef['id']]['title'] . ' : ' . $audits[$auditRef['id']]['score']);
          if (!empty($measure_id)) {
            $this->insertScoreData($measure_id, trim($auditRef['id']), $audits[$auditRef['id']]['score']);
          }
        }
      }

      $this->output->writeln('--------------------------------------------------------------------------------------');

    }
    else {
      $measure_id = $this->insertMeasureData($url_id, $screen, 0);
      if ($measure_id) {
        $this->output->writeln('Problem in fetching data.');
      }
      $response_body = $response->getBody();
      $decoded = Json::decode($response_body);
      $this->output->writeln($decoded['error']['message']);
    }

    return 1;
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
  protected function getUrlId($url) {
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
  protected function insertUrlData($url) {
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
   * @param string $screen
   *   The device type.
   * @param bool $status
   *   The entry status.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   The measure id of newly entered metric.
   *
   * @throws \Exception
   */
  protected function insertMeasureData($url_id, $screen, $status) {
    $measure_id = $this->database->insert('google_page_speed_measure_attempts')
      ->fields(['url_id', 'device', 'created', 'status'])
      ->values([
        $url_id,
        $screen,
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
  protected function insertScoreData($measure_id, $reference, $value) {
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

}
