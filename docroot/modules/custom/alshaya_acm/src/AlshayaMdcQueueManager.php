<?php

namespace Drupal\alshaya_acm;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class AlshayMdcQueueManager.
 *
 * @package Drupal\alshaya_acm
 */
class AlshayaMdcQueueManager {
  /**
   * HttpClientFactory instance.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  private $clientFactory;

  /**
   * Config Factory instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Logger channel factory instance.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * AlshayaAcmDashaboardManager constructor.
   *
   * @param \Drupal\Core\Http\ClientFactory $clientFactory
   *   HttpClientFactory instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory instance.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger Channel factory instance.
   */
  public function __construct(ClientFactory $clientFactory, ConfigFactoryInterface $configFactory, LoggerChannelFactoryInterface $logger) {
    $this->clientFactory = $clientFactory;
    $this->configFactory = $configFactory;
    $this->logger = $logger->get('alshaya_acm');
  }

  /**
   * Helper function to get MDC queue statistics.
   *
   * @param string $queue
   *   Machine name of queue for which stats being looked up for.
   *
   * @return \Psr\Http\Message\ResponseInterface|bool
   *   Response from MDC queue.
   */
  public function getMdcQueueStats($queue) {
    if (!$queue) {
      $this->logger->error('No Queue name specified.');
      return FALSE;
    }

    $mdc_config = $this->configFactory->get('alshaya_api.settings');

    $brand_module = $this->configFactory->get('alshaya.installed_brand')->get('module');
    $brand_name = str_replace('alshaya_', '', $brand_module);
    $brand_name = ($brand_name == 'hm') ? 'hnm' : $brand_name;

    $rabbitmq_directory = $mdc_config->get('rabbitmq_credentials_directory');

    $rabbitmq_creds = json_decode(file_get_contents($rabbitmq_directory . '/credentials.json'));
    $clientConfig = [
      'base_uri' => $rabbitmq_creds->base_uri,
      'timeout'  => 5,
      'auth' => [
        $rabbitmq_creds->username,
        $rabbitmq_creds->password,
      ],
    ];

    try {
      $endpoint = 'api/queues/' . $rabbitmq_creds->username . '/' . $queue . '.' . $brand_name;

      $client = $this->clientFactory->fromOptions($clientConfig);
      $response = $client->get($endpoint);
      $result = $response->getBody()->getContents();
    }
    catch (\Exception $e) {
      $result = NULL;
      $this->logger->error('Exception while invoking API @api. Message: @message.', [
        '@api' => $endpoint,
        '@message' => $e->getMessage(),
      ]);
    }

    return $result;
  }

}
