<?php
/**
 * @file
 * Contains Drupal\acq_commerce\Conductor\ClientFactory
 */

namespace Drupal\acq_commerce\Conductor;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Http\ClientFactory as DrupalClientFactory;
use GuzzleHttp\Client;

/**
 * Class ClientFactory
 * @package Drupal\acq_commerce\Conductor
 * @ingroup acq_commerce
 */
final class ClientFactory {

  /**
   * Guzzle HttpClient Factory
   * @var DrupalClientFactory $clientFactory
   */
  private $clientFactory;

  /**
   * Drupal Config Factory
   * @var ConfigFactory $configFactory
   */
  private $configFactory;

  /**
   * Constructor
   *
   * @param DrupalClientFactory $clientFactory
   * @param ConfigFactory $configFactory
   *
   * @return void
   */
  public function __construct(DrupalClientFactory $clientFactory, ConfigFactory $configFactory)
  {
        $this->clientFactory = $clientFactory;
        $this->configFactory = $configFactory;
  }

  /**
   * createAgentClient
   *
   * Create a Guzzle http client configured to connect to the
   * Conductor Agent (sync) instance from the site configuration.
   *
   * @return Client $client
   * @throws \InvalidArgumentException
   */
  public function createAgentClient()
  {
    $config = $this->configFactory->get('acq_commerce.conductor');
    if (!strlen($config->get('url_agent'))) {
      throw new \InvalidArgumentException('No Conductor Agent URL specified.');
    }

    $clientConfig = [
      'base_uri' => $config->get('url_agent'),
      'timeout'  => (int) $config->get('timeout'),
      'verify'   => (bool) $config->get('verify_ssl')
    ];

    return $this->clientFactory->fromOptions($clientConfig);
  }

  /**
   * createIngestClient
   *
   * Create a Guzzle http client configured to connect to the
   * Conductor Ingest (async) instance from the site configuration.
   *
   * @return Client $client
   * @throws \InvalidArgumentException
   */
  public function createIngestClient()
  {
    $config = $this->configFactory->get('acq_commerce.conductor');
    if (!strlen($config->get('url_ingest'))) {
      throw new \InvalidArgumentException('No Conductor Ingest URL specified.');
    }

    $clientConfig = [
      'base_uri' => $config->get('url_ingest'),
      'timeout'  => (int) $config->get('timeout'),
      'verify'   => (bool) $config->get('verify_ssl')
    ];

    return $this->clientFactory->fromOptions($clientConfig);
  }
}
