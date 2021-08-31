<?php

namespace Drupal\alshaya_vat;

use GuzzleHttp\Client;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class Alshaya Vat Certificate.
 *
 * @package Drupal\alshaya_vat
 */
class VatCertificateManager {

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Cache Backend service for storing vat data.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * VatCertificateManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The lnaguage manager service.
   * @param \GuzzleHttp\Client $http_client
   *   GuzzleHttp\Client object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_vat
   *   Cache Backend service for for storing vat data.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManager $languageManager, Client $http_client, LoggerChannelFactoryInterface $logger_factory, CacheBackendInterface $cache_vat) {
    $this->vatUrl = $config_factory->get('alshaya_vat.settings')->get('url');
    $this->languageManager = $languageManager;
    $this->httpClient = $http_client;
    $this->logger = $logger_factory->get('alshaya_vat');
    $this->cacheBackend = $cache_vat;
  }

  /**
   * Get vat data.
   *
   * @return array
   *   Vat data.
   */
  public function getVatData() {
    if (empty($this->vatUrl)) {
      return [];
    }
    $vat_data = [];
    // Create a custom cache tag.
    $cid = 'vat_certificate:' . $this->languageManager->getCurrentLanguage()->getId();
    // Check if there is any cache item associated with this cache tag.
    $data_cached = $this->cacheBackend->get($cid);
    if (!$data_cached) {
      try {
        $response = $this->httpClient->request('GET', $this->vatUrl);
        $result = $response->getBody()->getContents();

        if (empty($result)) {
          $this->logger->error('Something went wrong while fetching a VAT data at @api. Empty body content.', [
            '@api' => $this->vatUrl,
          ]);

          return $vat_data;
        }

        $output = json_decode($result, TRUE);
        $language_code = $this->languageManager->getCurrentLanguage()->getId();
        $vat_text_url = $output['vat_certificate']['text'][$language_code];
        $vat_text_url_split = explode('@', $vat_text_url);

        $vat_data['number'] = $output['vat_certificate']['number'][$language_code];
        $vat_data['text'] = trim($vat_text_url_split[0]);
        $vat_data['url'] = $vat_text_url_split[1];
        $vat_data['langcode'] = $language_code;
        // Store the data into the cache.
        $this->cacheBackend->set($cid, $vat_data, CacheBackendInterface::CACHE_PERMANENT, ['alshaya_vat_certificate']);
      }
      catch (\Exception $e) {
        $this->logger->error('Exception while while fetching a VAT data @api. Message: @message.', [
          '@api' => $this->vatUrl,
          '@message' => $e->getMessage(),
        ]);
      }
    }
    else {
      $vat_data = $data_cached->data;
    }

    return $vat_data;
  }

}
