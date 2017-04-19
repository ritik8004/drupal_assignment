<?php

namespace Drupal\acq_commerce\Conductor;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Http\ClientFactory as DrupalClientFactory;

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

}
