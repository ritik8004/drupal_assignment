<?php

namespace Drupal\alshaya_api;

use Drupal\Core\Language\LanguageManagerInterface;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Client;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides service for AlshayGraphqlApiWrapper.
 */
class AlshayGraphqlApiWrapper {

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
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * AlshayGraphqlApiWrapper constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   * @param \GuzzleHttp\Client $http_client
   *   GuzzleHttp\Client object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory,
                              Client $http_client,
                              ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager) {
    $this->logger = $logger_factory->get('alshaya_api');
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * Helper function to do graphql request from backend.
   *
   * @param string $method
   *   Request method.
   * @param array $fields
   *   Request URL.
   *
   * @return mixed
   *   Response data.
   *
   * @throws \Exception
   */
  public function doGraphqlRequest(string $method, array $fields = []) {
    $result = NULL;
    $request_options = [];
    $alshaya_api_config = $this->configFactory->get('alshaya_api.settings');
    // Get Current language.
    $current_language = $this->languageManager->getCurrentLanguage()->getId();
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

    // Add json header by default.
    $request_options['headers'] = [
      'Content-Type' => 'application/json',
      'store' => $alshaya_api_config->get('magento_lang_prefix')[$current_language],
    ];

    // Add query to body in request.
    $request_options['body'] = json_encode($fields);

    // Magento URL to get the product option attributes.
    $request_url = $alshaya_api_config->get('magento_host') . '/graphql';

    try {
      $response = $this->httpClient->request(
        $method,
        $request_url,
        $request_options
      );

      $result = $response->getBody()->getContents();

      if (empty($result)) {
        $this->logger->error('Something went wrong while invoking GraphQL API @api. Empty body content.', [
          '@api' => $request_url,
        ]);

        return NULL;
      }

      $result = json_decode($result, TRUE);
      $result = $result['data'] ?? [];
    }
    catch (\Exception $e) {
      $this->logger->error('Some exceptions are found while invoking GraphQL api @message', [
        'message' => $e->getMessage(),
      ]);
    }

    return $result;
  }

}
