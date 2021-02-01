<?php

namespace Drupal\alshaya_bazaar_voice\Service;

use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Client;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * BazaarVoiceApiWrapper constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   * @param \GuzzleHttp\Client $http_client
   *   GuzzleHttp\Client object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory,
                              Client $http_client,
                              ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->logger = $logger_factory->get('bazaar_voice_algolia');
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
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
   * @param string $skus
   *   batch of Skus.
   *
   * @return array
   *   BV api url with query parameters.
   */
  public function getBvUrl($endpoint, $skus) {
    $config = $this->configFactory->get('bazaar_voice.settings');

    return [
      'url' => $config->get('api_base_url') . '/' . $endpoint,
      'query' => [
        'apiversion' => $config->get('api_version'),
        'passkey' => $config->get('conversations_apikey'),
        'locale' => $config->get('locale'),
        'Include' => 'Products',
        'Stats' => 'Reviews',
        'Filter' => 'productid:' . $skus,
      ],
    ];
  }

  /**
   * Get reviews data from BV api.
   *
   * @param array $skus
   *   batch of Skus.
   *
   * @return array
   *   BV attributes data to be indexed in algolia.
   */
  public function getDataFromBvReviewFeeds(array $skus) {
    $skus = implode(",", $skus);
    $request = $this->getBvUrl('data/reviews.json', $skus);
    $url = $request['url'];
    $request_options['query'] = $request['query'];

    $result = $this->doRequest('GET', $url, $request_options);

    if (!$result['HasErrors'] && isset($result['Includes']['Products'])) {
      $response = [];
      foreach ($result['Includes']['Products'] as $value) {
        $response['ReviewStatistics'][$value['Id']] = [
          'AverageOverallRating' => $value['ReviewStatistics']['AverageOverallRating'],
          'TotalReviewCount' => $value['ReviewStatistics']['TotalReviewCount'],
          'RatingDistribution' => $this->processRatingDistribution($value['ReviewStatistics']['RatingDistribution']),
        ];
      }
      return $response;
    }

    return NULL;
  }

  /**
   * Get ratings range for particular product.
   *
   * @param array $rating
   *   Rating range.
   *
   * @return array
   *   A rating range for algolia rating facet.
   */
  public function processRatingDistribution(array $rating) {
    if (empty($rating)) {
      return NULL;
    }

    usort($rating, function ($rating_value1, $rating_value2) {
      return $rating_value2['RatingValue'] <=> $rating_value1['RatingValue'];
    });

    $rating_range = [];
    foreach ($rating as $value) {
      $rating_range[] = 'rating_' . $value['RatingValue'] . '_' . $value['Count'];
    }

    return $rating_range;
  }

}
