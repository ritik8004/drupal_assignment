<?php

namespace App\Translation;

use App\Helper\APIHelper;
use App\Helper\APIServicesUrls;
use GuzzleHttp\Client;

/**
 * Class TranslationHelper.
 *
 * @package App\Helper
 */
class TranslationHelper extends APIHelper {

  /**
   * Gets API translation for project.
   *
   * @return mixed
   *   API translations.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getTranslationFromApi() {
    $data = [];
    try {
      $headers = [
        'Content-Type' => 'application/json',
        'x-api-key' => $this->getTranslationApiKey(),
      ];
      $options = [
        'json' => [
          'project' => $this->getTranslationProjectName(),
        ],
        'headers' => $headers,
      ];
      $client = new Client([
        'base_uri' => $this->getTimetradeBaseUrl('translation'),
      ]);
      $response = $client->request(
        'POST',
        APIServicesUrls::TRANSLATION_SERVICE_URL_ALL,
        $options
      );
      $data = json_decode($response->getBody()->getContents());
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting translation. Message: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
    return $data;
  }

  /**
   * Get translation API key.
   *
   * @return mixed
   *   Translation API key.
   *
   * @throws \Exception
   */
  private function getTranslationApiKey() {
    $settings = $this->settings->getSettings('appointment_settings');
    if (empty($settings['translation_api_key'])) {
      throw new \Exception('Timetrade translation api key is not set.');
    }

    return $settings['translation_api_key'];
  }

  /**
   * Get Translation Poject name.
   *
   * @return mixed
   *   Project name.
   *
   * @throws \Exception
   */
  private function getTranslationProjectName() {
    $settings = $this->settings->getSettings('appointment_settings');
    if (empty($settings['project'])) {
      throw new \Exception('Timetrade translation project name is not set.');
    }

    return $settings['project'];
  }

  /**
   * Gets String translation from API result.
   */
  public function getTranslation($string, $langcode) {
    if (!$this->isValidLangcode($langcode) || $langcode == 'en') {
      return $string;
    }

    $translations = $this->cache->getItem('translations');
    if (empty($translations)) {
      $translations = (array) $this->getTranslationFromApi()->{'en-ar'};
      $this->cache->setItem('translations', $translations);
    }

    return $translations[$string] ?? $string;
  }

  /**
   * Translates Store address.
   */
  public function getAddressTranslation($address, $langcode) {
    if (!$this->isValidLangcode($langcode) || $langcode == 'en') {
      return $address;
    }
    $translated_address = new \stdClass();
    foreach ($address as $key => $item) {
      $translated_address->{$key} = $this->getTranslation($item, $langcode);
    }
    return $translated_address;
  }

}
