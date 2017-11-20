<?php

namespace Drupal\acq_commerce\Conductor;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Http\ClientFactory as DrupalClientFactory;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\HandlerStack;

/**
 * Class ClientFactory.
 *
 * @package Drupal\acq_commerce\Conductor
 *
 * @ingroup acq_commerce
 */
final class ClientFactory {

  /**
   * Guzzle HttpClient Factory.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  private $clientFactory;

  /**
   * Drupal Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $configFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Http\ClientFactory $clientFactory
   *   ClientFactory object.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory object.
   */
  public function __construct(DrupalClientFactory $clientFactory, ConfigFactory $configFactory) {

    $this->clientFactory = $clientFactory;
    $this->configFactory = $configFactory;
  }

  /**
   * CreateAgentClient.
   *
   * Create a Guzzle http client configured to connect to the
   * Conductor Agent (sync) instance from the site configuration.
   *
   * @return \GuzzleHttp\Client
   *   Object of initialized client.
   *
   * @throws \InvalidArgumentException
   */
  public function createAgentClient() {

    $config = $this->configFactory->get('acq_commerce.conductor');
    if (!strlen($config->get('url_agent'))) {
      throw new \InvalidArgumentException('No Conductor Agent URL specified.');
    }

    $clientConfig = [
      'base_uri' => $config->get('url_agent'),
      'timeout'  => (int) $config->get('timeout'),
      'verify'   => (bool) $config->get('verify_ssl'),
    ];

    return $this->clientFactory->fromOptions($clientConfig);
  }

  /**
   * CreateIngestClient.
   *
   * Create a Guzzle http client configured to connect to the
   * Conductor Ingest (async) instance from the site configuration.
   *
   * @return \GuzzleHttp\Client
   *   Object of initialized client.
   *
   * @throws \InvalidArgumentException
   */
  public function createIngestClient() {

    $config = $this->configFactory->get('acq_commerce.conductor');
    if (!strlen($config->get('url_ingest'))) {
      throw new \InvalidArgumentException('No Conductor Ingest URL specified.');
    }

    $clientConfig = [
      'base_uri' => $config->get('url_ingest'),
      'timeout'  => (int) $config->get('timeout'),
      'verify'   => (bool) $config->get('verify_ssl'),
    ];

    return $this->clientFactory->fromOptions($clientConfig);
  }

  /**
   * CreateClient.
   *
   * Create a Guzzle http client configured to connect to the
   * Conductor instance from the site configuration.
   *
   * @return \GuzzleHttp\Client
   *   Object of initialized client.
   *
   * @throws \InvalidArgumentException
   */
  public function createClient() {

    $config = $this->configFactory->get('acq_commerce.conductor');
    if (!strlen($config->get('url'))) {
      throw new \InvalidArgumentException('No Conductor URL specified.');
    }

    // Create key and middleware.
    $key = new Key($config->get('hmac_id'), $config->get('hmac_secret'));
    $middleware = new HmacAuthMiddleware($key);
    // Register the middleware.
    $stack = HandlerStack::create();
    $stack->push($middleware);

    $clientConfig = [
      'base_uri' => $config->get('url'),
      'timeout'  => (int) $config->get('timeout'),
      'verify'   => (bool) $config->get('verify_ssl'),
      'handler'  => $stack,
    ];

    return $this->clientFactory->fromOptions($clientConfig);
  }

}
