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
    'screen' => ['desktop', 'mobile'],
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
    foreach ($urls as $url) {
      foreach ($options['screen'] as $screen) {
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

    $response = $client->get($siteUrl, ['http_errors' => FALSE]);

    if ($response->getStatusCode() == 200) {
      $response_body = $response->getBody();
      $decoded = Json::decode($response_body);
      $categories = $decoded['lighthouseResult']['categories'];
      $audits = $decoded['lighthouseResult']['audits'];

      // Writing data on terminal.
      $this->output->writeln('URL: ' . $url);
      $this->output->writeln('Screen: ' . $screen);
      $this->output->writeln('Performance Score: ' . $categories['performance']['score']);

      // Fetch url_id if present.
      $url_id = $this->getUrlId($url);
      if (empty($url_id)) {
        $url_id = $this->insertUrlData($url);
      }

      // Check if performance score is present.
      $metric_id = $this->getMetricId('pf_score');
      if (empty($metric_id)) {
        $metric_id = $this->insertMetricData('pf_score');
      }

      $score_id = $this->insertScoreData($metric_id, $url_id, $screen, $categories['performance']['score']);
      if (!isset($score_id) || is_null($score_id) || empty($score_id)) {
        $this->output->writeln('Problem in database insert.');
      }

      foreach ($categories['performance']['auditRefs'] as $auditRef) {
        if ($auditRef['weight'] > 0) {
          $this->output->writeln($audits[$auditRef['id']]['title'] . ' : ' . $audits[$auditRef['id']]['score']);

          $metric_id = $this->getMetricId($auditRef['id']);

          if (empty($metric_id)) {
            $metric_id = $this->insertMetricData($auditRef['id']);
          }

          if (!empty($url_id) && !empty($metric_id)) {
            $score_id = $this->insertScoreData($metric_id, $url_id, $screen, $audits[$auditRef['id']]['score']);
            if (!isset($score_id) || is_null($score_id) || empty($score_id)) {
              $this->output->writeln('Problem in database insert.');
            }
          }
        }
      }

      $this->output->writeln('--------------------------------------------------------------------------------------');

    }
    else {
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
   * To check if metric is present or not.
   *
   * @param string $reference
   *   The metric to check.
   *
   * @return int
   *   The found metric id .
   */
  protected function getMetricId($reference) {
    $drush_select = $this->database->select('google_page_speed_metrics', 'gps_metrics');
    $drush_select->fields('gps_metrics', ['metric_id']);
    $drush_select->condition('reference', trim($reference));
    $metric_id = $drush_select->execute()->fetchField();
    return $metric_id;
  }

  /**
   * To insert new metric.
   *
   * @param string $reference
   *   The metric name.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   The metric id of newly entered metric.
   *
   * @throws \Exception
   */
  protected function insertMetricData($reference) {
    $metric_id = $this->database->insert('google_page_speed_metrics')
      ->fields(['category', 'reference'])
      ->values(['performance', trim($reference)])
      ->execute();
    return $metric_id;
  }

  /**
   * To insert the score based on metric, time and url.
   *
   * @param int $metric_id
   *   The metric id of metric.
   * @param int $url_id
   *   The url is of url.
   * @param string $screen
   *   The device type.
   * @param string $value
   *   The score value.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   The id of newly inserted score.
   *
   * @throws \Exception
   */
  protected function insertScoreData($metric_id, $url_id, $screen, $value) {
    $score_id = $this->database->insert('google_page_speed_scores')
      ->fields(['created', 'metric_id', 'url_id', 'device', 'value'])
      ->values([
        $this->time->getRequestTime(),
        $metric_id,
        $url_id,
        $screen,
        $value,
      ])
      ->execute();
    return $score_id;
  }

}
