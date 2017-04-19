<?php

namespace Drupal\acq_commerce\Conductor;

use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * IngestAPIWrapper class.
 */
class IngestAPIWrapper {

  use \Drupal\acq_commerce\Conductor\IngestRequestTrait;

  /**
   * Constructor.
   *
   * @param ClientFactory $client_factory
   *   Object of ClientFactory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   Object of LoggerFactory.
   */
  public function __construct(ClientFactory $client_factory, LoggerChannelFactory $logger_factory) {

    $this->clientFactory = $client_factory;
    $this->logger = $logger_factory->get('acq_sku');
  }

  /**
   * Performs full conductor sync.
   */
  public function productFullSync() {
    $endpoint = "product/sync";

    $doReq = function ($client, $opt) use ($endpoint) {
      return $client->post($endpoint, $opt);
    };

    try {
      $this->tryIngestRequest($doReq, 'productFullSync', 'products');
    }
    catch (ConductorException $e) {
    }
  }

}
