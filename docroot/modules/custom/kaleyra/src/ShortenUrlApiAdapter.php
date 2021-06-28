<?php

namespace Drupal\kaleyra;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provides function to get short url using TXTLY service.
 */
class ShortenUrlApiAdapter {

  /**
   * Guzzle Http Client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * ShortenUrlApiAdapter constructor.
   *
   * @param \GuzzleHttp\Client $client
   *   Guzzle Http Client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger channel factory.
   */
  public function __construct(Client $client,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactoryInterface $logger_channel_factory) {
    $this->client = $client;
    $this->configFactory = $config_factory;
    $this->logger = $logger_channel_factory->get('kaleyra.ShortenUrlApiAdapter');
  }

  /**
   * Helper function to send SMS using Kaleyra.
   *
   * @param string $long_url
   *   Long URL to shorten.
   *
   * @return string
   *   Short URL (returns long url as is in case of exception).
   */
  public function getShortUrl(string $long_url): string {
    $short_url = $long_url;
    $kaleyra_settings = $this->configFactory->get('kaleyra.settings');
    $api_base_url = $kaleyra_settings->get('api_domain');
    $api_version = $kaleyra_settings->get('api_version');
    $query = [
      'method' => 'txtly.create',
      'url' => $long_url,
      'api_key' => $kaleyra_settings->get('api_key'),
    ];

    try {
      $response = $this->client->request('GET', $api_base_url . '/' . $api_version, ['query' => $query]);
      $response_content = $response->getBody()->getContents();

      $result = json_decode($response_content, TRUE);
      if ($result['status']) {
        return $result['txtly'];
      }

      // Log the error and we will keep using original url as is.
      $this->logger->error('Response from Kaleyra when trying to get short url for link !link: response: @response', [
        '!link' => $long_url,
        '@response' => $response_content,
      ]);
    }
    catch (GuzzleException $e) {
      $this->logger->error('Failed to get short url for !link with the following error !message.', [
        '!link' => $long_url,
        '!message' => $e->getMessage(),
      ]);
    }

    return $short_url;
  }

}
