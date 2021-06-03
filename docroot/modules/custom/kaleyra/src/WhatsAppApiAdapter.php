<?php

namespace Drupal\kaleyra;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Whatsapp Api Adapter.
 *
 * @todo support other type of messages, for now it supports simple text only.
 */
class WhatsAppApiAdapter {

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
   * Helper function to send WhatsApp message using Kaleyra.
   *
   * @param string $to
   *   Phone number to send WhatsApp message.
   * @param string $template
   *   Template to use.
   * @param array $params
   *   Dynamic parameters.
   * @param string $language
   *   Language code.
   */
  public function sendUsingTemplate(string $to, string $template, array $params, string $language) {
    $kaleyra_settings = $this->configFactory->get('kaleyra.settings');

    $sid = $kaleyra_settings->get('whatsapp_sid');
    $api_url = sprintf('https://api.kaleyra.io/v1/%s/messages', $sid);

    $request_options['headers']['api-key'] = $kaleyra_settings->get('whatsapp_api_key');
    if (empty($request_options['headers']['api-key'])) {
      $request_options['headers']['api-key'] = $kaleyra_settings->get('api_key');
    }

    $request_options['json'] = [
      'method' => 'wa',
      'channel' => 'WhatsApp',
      'type' => 'template',
      'template_name' => $template,
      'from' => $kaleyra_settings->get('whatsapp_from'),
      'to' => $to,
      // @todo check if we need to support quotes here.
      'params' => '"' . implode('", "', $params) . '"',
      'lang_code' => $language,
    ];

    try {
      $response = $this->client->request('POST', $api_url, $request_options);

      $this->logger->notice('Response from Kaleyra when sending message to @to: response: @response', [
        '@to' => $to,
        '@response' => $response->getBody()->getContents(),
      ]);

      return TRUE;
    }
    catch (GuzzleException $e) {
      $this->logger->error('Failed sending whatsapp message to @to with the following error @message.', [
        '@to' => $to,
        '@message' => $e->getMessage(),
      ]);
    }

    return FALSE;
  }

}
