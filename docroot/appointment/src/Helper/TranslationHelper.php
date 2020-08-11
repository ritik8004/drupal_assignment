<?php

namespace App\Helper;

use App\Cache\Cache;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use App\Service\Config\SystemSettings;

/**
 * Class TranslationHelper.
 *
 * @package App\Helper
 */
class TranslationHelper extends APIHelper {

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $appointmentSettings;

  /**
   * ConfigurationServices constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \App\Cache\Cache $cache
   *   Cache Helper.
   */
  public function __construct(LoggerInterface $logger,
                              SystemSettings $settings,
                              Cache $cache) {
    parent::__construct($logger, $settings, $cache);
    $this->appointmentSettings = $settings->getSettings('appointment_settings');
  }

  /**
   * Checks if langcode is valid.
   *
   * @return mixed
   *   If true then langcode, otherwise false.
   */
  public function isValidLangcode($langcode) {
    $langcodes = ['en', 'ar'];
    if (in_array($langcode, $langcodes)) {
      return $langcode;
    }
    return FALSE;
  }

  /**
   * Gets API translation for project.
   *
   * @return mixed
   *   API translations.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getTranslationFromTimetradeApi() {
    $base_uri = $this->getTimetradeBaseUrl('translation');
    $endpoint = '/api/v1/project/get';
    $headers = [
      'Content-Type' => 'application/json',
      'x-api-key' => $this->getTranslationApiKey(),
    ];
    $options = [
      'json' => [
        "project" => $this->getTranslationProjectName(),
      ],
      'headers' => $headers,
    ];
    $client = new Client([
      'base_uri' => $base_uri,
    ]);
    $response = $client->request('POST', $endpoint, $options);
    return json_decode($response->getBody()->getContents());
  }

  /**
   * Get translation API key.
   *
   * @return mixed
   *   Translation API key.
   *
   * @throws \Exception
   */
  public function getTranslationApiKey() {
    $translationApiKey = $this->appointmentSettings['translation_api_key'];
    if (empty($translationApiKey)) {
      throw new \Exception('Timetrade translation api key is not set.');
    }

    return $translationApiKey;
  }

  /**
   * Get Translation Poject name.
   *
   * @return mixed
   *   Project name.
   *
   * @throws \Exception
   */
  public function getTranslationProjectName() {
    $project = $this->appointmentSettings['project'];
    if (empty($project)) {
      throw new \Exception('Timetrade translation project name is not set.');
    }

    return $project;
  }

  /**
   * Gets String translation from API result.
   */
  public function getTranslation($string, $langcode) {
    // @todo update for passing translation from API result.
  }

}
