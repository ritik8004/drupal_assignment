<?php

namespace Drupal\dynamic_yield\Service;

use GuzzleHttp\Client;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\TransferStats;

/**
 * API Wrapper for DY Product delta feed.
 *
 * @package Drupal\dynamic_yield\Service
 */
class ProductDeltaFeedApiWrapper {
  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The LoggerFactory object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * ProductDeltaFeedApiWrapper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \GuzzleHttp\Client $http_client
   *   GuzzleHttp\Client object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              Client $http_client,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
    $this->logger = $logger_factory->get('dynamic_yield');
  }

  /**
   * Get DY Product feed api url based on action.
   *
   * @param string $feedId
   *   Feed Id.
   * @param string $itemId
   *   Item Id (SKU).
   * @param string $action
   *   API action.
   *
   * @return string
   *   API URL.
   */
  private function getDyProductFeedApiUrl(string $feedId, string $itemId, string $action = '') {
    $hostUrl = $this->configFactory->get('dynamic_yield.settings')->get('host_url') ?? '';

    if (empty($hostUrl) || empty($feedId) || empty($itemId)) {
      $this->logger->info('Host URL, Feed Id and Item Id is required to build DY product feed API URL. Host URL: @hostUrl. Feed Id: @feedId. Item Id: @itemId.', [
        '@hostUrl' => $hostUrl,
        '@feedId' => $feedId,
        '@itemId' => $itemId,
      ]);

      return '';
    }

    $apiUrl = $hostUrl . '/v2/feeds/' . $feedId . '/' . $itemId;

    if ($action === 'partial_update') {
      $apiUrl = $apiUrl . '/partial';
    }

    return $apiUrl;
  }

  /**
   * Function to invoke DY Product Delta feed API.
   *
   * @param string $url
   *   URL.
   * @param string $method
   *   Request method - get/post/put/delete.
   * @param array $options
   *   Options to send to the request.
   *
   * @return array|null
   *   Response from the API.
   */
  private function invokeProductFeedApi(string $url, string $method = 'POST', array $options = []) {
    if (empty($url)) {
      $this->logger->error('URL is required to invoke DY product feed API.');
      return NULL;
    }

    $that = $this;
    $options['on_stats'] = function (TransferStats $stats) use ($that) {
      $code = ($stats->hasResponse())
        ? $stats->getResponse()->getStatusCode()
        : 0;

      $that->logger->info(sprintf(
        'Finished API request %s in %.4f. Response code: %d. Method: %s;',
        $stats->getEffectiveUri(),
        $stats->getTransferTime(),
        $code,
        $stats->getRequest()->getMethod()
      ));
    };

    try {
      $response = $this->httpClient->request($method, $url, $options);
      $result = $response->getBody()->getContents();

      if (empty($result)) {
        $this->logger->error('Something went wrong while invoking DY product feed API @api. Empty body content.', [
          '@api' => $url,
        ]);
        return NULL;
      }

      $decoded_result = json_decode($result, TRUE);

      if (!empty($decoded_result['success'])) {
        $this->logger->info('Invoked DY product feed API @api. Transaction Id: @transaction_id.', [
          '@api' => $url,
          '@transaction_id' => $decoded_result['data']['transaction_id'],
        ]);
      }

      return $decoded_result;
    }
    catch (\Exception $e) {
      $this->logger->error('Exception while invoking DY product feed API @api. Message: @message.', [
        '@api' => $url,
        '@message' => $e->getMessage(),
      ]);
    }

    return NULL;
  }

  /**
   * Wrapper function to invoke delete API.
   *
   * @param string $apiKey
   *   API Key.
   * @param string $feedId
   *   Feed Id.
   * @param string $itemId
   *   Item Id (SKU).
   *
   * @return array|null
   *   API Response.
   */
  public function productFeedDelete(string $apiKey, string $feedId, string $itemId) {
    if (empty($apiKey) || empty($feedId) || empty($itemId)) {
      $this->logger->info('API key, Feed Id and Item Id is required. API Key: @apiKey. Feed Id: @feedId. Item Id: @itemId.', [
        '@apiKey' => $apiKey,
        '@feedId' => $feedId,
        '@itemId' => $itemId,
      ]);
      return NULL;
    }

    $url = $this->getDyProductFeedApiUrl($feedId, $itemId);
    $options = [
      'headers' => [
        'DY-API-key' => $apiKey,
      ],
    ];
    $response = $this->invokeProductFeedApi($url, 'DELETE', $options);

    return $response;
  }

  /**
   * Wrapper function to invoke Partial API.
   *
   * @param string $apiKey
   *   API Key.
   * @param string $feedId
   *   Feed Id.
   * @param string $itemId
   *   Item Id (SKU).
   * @param array $data
   *   List of fields and values to update feed.
   *
   * @return array|null
   *   API Response.
   */
  public function productFeedPartialUpdate(string $apiKey, string $feedId, string $itemId, array $data) {
    if (empty($apiKey) || empty($feedId) || empty($itemId) || empty($data)) {
      $this->logger->info('API key, Feed Id, Item Id and Data is required. API Key: @apiKey. Feed Id: @feedId. Item Id: @itemId. Data: @data.', [
        '@apiKey' => $apiKey,
        '@feedId' => $feedId,
        '@itemId' => $itemId,
        '@data' => json_encode($data),
      ]);
      return NULL;
    }

    $url = $this->getDyProductFeedApiUrl($feedId, $itemId, 'partial_update');
    $options = [
      'headers' => [
        'DY-API-key' => $apiKey,
      ],
      'body' => json_encode($data),
    ];
    $response = $this->invokeProductFeedApi($url, 'POST', $options);

    return $response;
  }

  /**
   * Wrapper function to invoke upsert API.
   *
   * Upsert - To insert a new item into feed or fully override an existing item.
   *
   * @param string $apiKey
   *   API Key.
   * @param string $feedId
   *   Feed Id.
   * @param string $itemId
   *   Item Id (SKU).
   * @param array $data
   *   List of fields and values to insert/update feed.
   *
   * @return array|null
   *   API Response.
   */
  public function productFeedUpsert(string $apiKey, string $feedId, string $itemId, array $data) {
    if (empty($apiKey) || empty($feedId) || empty($itemId) || empty($data)) {
      $this->logger->info('API key, Feed Id, Item Id and Data is required. API Key: @apiKey. Feed Id: @feedId. Item Id: @itemId. Data: @data.', [
        '@apiKey' => $apiKey,
        '@feedId' => $feedId,
        '@itemId' => $itemId,
        '@data' => json_encode($data),
      ]);
      return NULL;
    }

    $url = $this->getDyProductFeedApiUrl($feedId, $itemId);
    $options = [
      'headers' => [
        'DY-API-key' => $apiKey,
      ],
      'body' => json_encode($data),
    ];
    $response = $this->invokeProductFeedApi($url, 'PUT', $options);

    return $response;
  }

}
