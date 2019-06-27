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
use Symfony\Component\Serializer\Serializer;

class GooglePageSpeedCommands extends DrushCommands
{
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
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheInvalidator;

  /**
   * The Serializer service object.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * GooglePageSpeedCommands constructor.
   * @param ConfigFactory $config_factory
   * @param Connection $database
   * @param CacheTagsInvalidatorInterface $cache_invalidator
   * @param Serializer $serializer
   */
  public function __construct(ConfigFactory $config_factory, Connection $database, CacheTagsInvalidatorInterface $cache_invalidator, Serializer $serializer)
  {
    parent::__construct();
    $this->configFactory = $config_factory;
    $this->database = $database;
    $this->cacheInvalidator = $cache_invalidator;
    $this->serializer = $serializer;
  }

  /**
   * Drush command to get insights data.
   *
   *
   * @command google_page_speed:getinsights
   * @aliases gps-gi
   * @options url An option that takes the target url.
   * @options screen An option that takes target screen
   * @usage google_page_speed:insights --url https://google.com --screen desktop
   *   Display data for https://google.com on screen desktop
   */
  public function insights($options = ['url' => '', 'screen' => ''])
  {
    $config = $this->configFactory->get(GooglePageSpeedConfigForm::CONFIG_NAME);
    $api_key = $config->get('api_key');
    if (empty($api_key)) {
      $this->output->writeln('Google API key is empty');
    }

    $client = new Client();
    $serializer = $this->serializer;

    // Get data from options.
    if (!empty($api_key) && !empty($options['url'])) {
      $options['screen'] = (empty($options['screen'])) ? 'desktop' : $options['screen'];
      $this->getPageSpeedData($client, $serializer, $api_key, $options['url'], $options['screen']);
    }
    // Get data from configurations.
    else{
      $urls = explode(PHP_EOL, $config->get('page_url'));
      $screens = $config->get('screen');
      if (isset($api_key, $urls, $screens, $client, $serializer)) {
        foreach ($urls as $url) {
          foreach ($screens as $screen) {
            try {
              if (!empty($api_key) && !empty($url) && !empty($screen)){
                $this->getPageSpeedData($client, $serializer, $api_key, trim($url), trim($screen));
              }
            } catch (RequestException $e) {
              return($this->t('Error'));
            }
          }
        }
      } else {
        $this->output->writeln('Arguments are empty');
      }
    }
    $this->cacheInvalidator->invalidateTags(['google-page-speed:block']);

  }

  /**
   * @param $client
   * @param $serializer
   * @param $api_key
   * @param $url
   * @param $screen
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|int
   * @throws \Exception
   */

  protected function getPageSpeedData($client, $serializer, $api_key, $url, $screen) {
    $siteUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?key=' . $api_key . '&url=' . $url . '&strategy=' . $screen;

    $response = $client->get($siteUrl, ['http_errors' => false]);

    if ($response->getStatusCode() == 200) {
      $response_body = $response->getBody();
      $decoded = Json::decode($response_body);
      $metrics_array = [$decoded['lighthouseResult']['audits']['metrics']['details']['items'][0]];
      $metrics = $serializer->serialize($metrics_array, 'json');
      $audits = $decoded['lighthouseResult']['audits'];
      $score_array = [
        $audits['first-contentful-paint']['displayValue'],
        $audits['first-meaningful-paint']['displayValue'],
        $audits['speed-index']['displayValue'],
        $audits['first-cpu-idle']['displayValue'],
        $audits['interactive']['displayValue'],
        $audits['max-potential-fid']['displayValue']
      ];
      $score = $serializer->serialize($score_array, 'json');

      $this->output->writeln('URL: ' . $url);
      $this->output->writeln('Screen: ' . $screen);
      $this->output->writeln('First Contentful Paint: ' . $audits['first-contentful-paint']['displayValue']);
      $this->output->writeln('First Meaningful Paint: ' . $audits['first-meaningful-paint']['displayValue']);
      $this->output->writeln('Speed Index: ' . $audits['speed-index']['displayValue']);
      $this->output->writeln('First CPU Idle: ' . $audits['first-cpu-idle']['displayValue']);
      $this->output->writeln('Interactive: ' . $audits['interactive']['displayValue']);
      $this->output->writeln('Maximum Potential First Input Delay: ' . $audits['max-potential-fid']['displayValue']);
      $this->output->writeln('--------------------------------------------------------------------------------------');

      $drush_insert = $this->database->insert('google_page_speed_data')
        ->fields([
          'url',
          'screen',
          'created',
          'metrics',
          'score'
        ])
        ->values([
          $url,
          $screen,
          \Drupal::time()
            ->getRequestTime(),
          $metrics,
          $score
        ])->execute();

      if (!isset($drush_insert) || is_null($drush_insert) || empty($drush_insert) ) {
        $this->output->writeln('Problem in database insert.');
      }
    }
    else {
      $response_body = $response->getBody();
      $decoded = Json::decode($response_body);
      $this->output->writeln($decoded['error']['message']);
    }

    return 1;
  }
}
