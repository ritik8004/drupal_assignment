<?php

namespace Drupal\alshaya_bazaar_voice\Service;

use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Client;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides integration with BazaarVoice.
 */
class AlshayaBazaarVoiceApiHelper {

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * BazaarVoiceApiWrapper constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   * @param \GuzzleHttp\Client $http_client
   *   GuzzleHttp\Client object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory,
                              Client $http_client,
                              ConfigFactoryInterface $config_factory) {
    $this->logger = $logger_factory->get('alshaya_bazaar_voice');
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
  }

  /**
   * Helper function to do BV API request.
   *
   * @param string $method
   *   Request method.
   * @param string $url
   *   Request URL.
   * @param array $request_options
   *   Request options (optional).
   *
   * @return mixed
   *   Response data.
   *
   * @throws \Exception
   */
  public function doRequest(string $method, string $url, array $request_options = []) {
    $request_options['on_stats'] = function (TransferStats $stats) {
      $code = ($stats->hasResponse())
        ? $stats->getResponse()->getStatusCode()
        : 0;

      $this->logger->info(sprintf(
        'Finished API request %s in %.4f. Response code: %d. Method: %s.',
        $stats->getEffectiveUri(),
        $stats->getTransferTime(),
        $code,
        $stats->getRequest()->getMethod()
      ));
    };

    try {
      $response = $this->httpClient->request(
        $method,
        $url,
        $request_options
      );

      $result = $response->getBody()->getContents();

      $result = is_string($result) && !empty($result)
        ? json_decode($result, TRUE)
        : $result;

      if ($result['HasErrors']) {
        // Log the error message.
        $this->logger->error('Error while doing BV api call. Error message: @message, Response code: @response_code.', [
          '@message' => json_encode($result['Errors']),
          '@response_code' => $response->getStatusCode(),
        ]);
      }
    }
    catch (ConnectException $e) {
      $this->logger->error($e->getMessage());
    }

    return $result;
  }

  /**
   * Get api url for BV.
   *
   * @param string $endpoint
   *   Endpoint.
   * @param array $extra_params
   *   Extra api parameters.
   *
   * @return array
   *   BV api url with query parameters.
   */
  public function getBvUrl($endpoint, array $extra_params) {
    $config = $this->configFactory->get('bazaar_voice.settings');
    $query = [
      'apiversion' => $config->get('api_version'),
      'passkey' => $config->get('conversations_apikey'),
      'locale' => $config->get('locale'),
    ];
    $query = array_merge($query, $extra_params);

    return [
      'url' => $config->get('api_base_url') . '/' . $endpoint,
      'query' => $query,
    ];
  }

}
