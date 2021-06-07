<?php

namespace Drupal\kaleyra;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * SMS Message Api Adapter.
 */
class MessageApiAdapter {

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
   * MessageApiAdapter constructor.
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
    $this->logger = $logger_channel_factory->get('kaleyra');
  }

  /**
   * Helper function to send SMS using Kaleyra.
   *
   * @param string $to
   *   Phone number to end SMS.
   * @param string $message
   *   SMS text.
   */
  public function send($to, $message) {
    $kaleyra_settings = $this->configFactory->get('kaleyra.settings');
    $api_base_url = $kaleyra_settings->get('api_domain');
    $api_version = $kaleyra_settings->get('api_version');
    $query = [
      'method' => 'sms',
      'sender' => $kaleyra_settings->get('sender_identifier'),
      'to' => $to,
      'message' => urlencode($message),
      'api_key' => $kaleyra_settings->get('api_key'),
      'unicode' => $kaleyra_settings->get('unicode'),
    ];

    try {
      $response = $this->client->request('GET', $api_base_url . '/' . $api_version, ['query' => $query]);

      $this->logger->notice('Response from Kaleyra when sending message to @to: response: @response', [
        '@to' => $to,
        '@response' => $response->getBody()->getContents(),
      ]);

      return TRUE;
    }
    catch (GuzzleException $e) {
      $this->logger->error('Failed sending message to !to with the following error !message.', [
        '!to' => $to,
        '!message' => $e->getMessage(),
      ]);
    }

    return FALSE;
  }

}
