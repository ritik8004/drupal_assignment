<?php

namespace Drupal\google_page_speed\Commands;

use Drush\Commands\DrushCommands;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\google_page_speed\Form\GooglePageSpeedConfigForm;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\google_page_speed\Service\GpsInsightsWrapper;

/**
 * Class Google Page Speed Commands.
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
   * CacheInvalidator object.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheInvalidator;

  /**
   * The GpsInsightsWrapper object.
   *
   * @var \Drupal\google_page_speed\Service\GpsInsightsWrapper
   */
  protected $gpsInsights;

  /**
   * Static reference to logger object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * GooglePageSpeedCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The ConfigFactory object to inject configfactory service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_invalidator
   *   The CacheInvalidator object to inject cache invalidator service.
   * @param \Drupal\google_page_speed\Service\GpsInsightsWrapper $gps_insights
   *   Injecting GpsInsights service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Injecting logger channel.
   */
  public function __construct(ConfigFactory $config_factory,
                              CacheTagsInvalidatorInterface $cache_invalidator,
                              GpsInsightsWrapper $gps_insights,
                              LoggerChannelInterface $logger) {
    parent::__construct();
    $this->configFactory = $config_factory;
    $this->cacheInvalidator = $cache_invalidator;
    $this->gpsInsights = $gps_insights;
    $this->logger = $logger;
  }

  /**
   * Drush command to get insights data.
   *
   * @command google_page_speed:getinsights
   * @aliases gps-gi
   * @options url An option that takes the target url.
   * @options device An option that takes target device
   * @usage google_page_speed:insights --url https://google.com --device desktop
   *   Display data for https://google.com on device desktop
   *
   * @throws \Exception
   */
  public function insights($options = [
    'url' => '',
    'device' => '',
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
      : explode(PHP_EOL, $config->get('page_urls'));

    $devices = !empty($options['device'])
      ? (array) $options['device']
      : $config->get('device');

    foreach ($urls as $url) {
      foreach ($devices as $device) {
        try {
          if (!empty($api_key) && !empty($url) && !empty($device)) {
            $this->getPageSpeedData($client, $api_key, trim($url), trim($device));
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
   * @param string $device
   *   The device for which data is needed.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|int
   *   The integer return statement.
   *
   * @throws \Exception
   *   Throws Exception.
   */
  protected function getPageSpeedData(Client $client, $api_key, $url, $device) {
    $siteUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?key=' . $api_key . '&url=' . $url . '&strategy=' . $device;
    $this->output->writeln('Fetching data....');
    $this->output->writeln('URL: ' . $url);
    $this->output->writeln('device: ' . $device);

    $response = $client->get($siteUrl, ['http_errors' => FALSE]);

    // Fetch url_id if present.
    $url_id = $this->gpsInsights->getUrlId($url);
    if (empty($url_id)) {
      $url_id = $this->gpsInsights->insertUrlData($url);
    }

    if ($response->getStatusCode() == 200) {
      $response_body = $response->getBody();
      $decoded = Json::decode($response_body);
      $categories = $decoded['lighthouseResult']['categories'];
      $audits = $decoded['lighthouseResult']['audits'];

      // Writing data on terminal.
      $this->output->writeln('Performance Score: ' . $categories['performance']['score']);
      $measure_id = $this->gpsInsights->insertMeasureData($url_id, $device, 1);
      $this->gpsInsights->insertScoreData($measure_id, 'pf_score', $categories['performance']['score']);
      foreach ($categories['performance']['auditRefs'] as $auditRef) {
        if ($auditRef['weight'] > 0) {
          $this->output->writeln($audits[$auditRef['id']]['title'] . ' : ' . $audits[$auditRef['id']]['score']);
          if (!empty($measure_id)) {
            $this->gpsInsights->insertScoreData($measure_id, trim($auditRef['id']), $audits[$auditRef['id']]['score']);
          }
        }
      }
      $this->output->writeln('--------------------------------------------------------------------------------------');
    }
    else {
      $measure_id = $this->gpsInsights->insertMeasureData($url_id, $device, 0);
      if ($measure_id) {
        $this->output->writeln('Problem in fetching data.');
        $this->logger->error(dt('Error in fetching data'));
      }
      $response_body = $response->getBody();
      $decoded = Json::decode($response_body);
      $this->output->writeln($decoded['error']['message']);
      $this->logger->error($decoded['error']['message']);
    }

    return 1;
  }

}
