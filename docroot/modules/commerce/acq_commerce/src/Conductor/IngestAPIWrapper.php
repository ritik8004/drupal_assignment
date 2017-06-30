<?php

namespace Drupal\acq_commerce\Conductor;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Config\ConfigFactoryInterface;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Object of ConfigFactoryInterface.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   Object of LoggerFactory.
   */
  public function __construct(ClientFactory $client_factory, ConfigFactoryInterface $config_factory, LoggerChannelFactory $logger_factory) {

    $this->clientFactory = $client_factory;
    $this->logger = $logger_factory->get('acq_sku');
    $this->apiVersion = $config_factory->get('acq_commerce.conductor')->get('api_version');
    $this->debug = $config_factory->get('acq_commerce.conductor')->get('debug');
    $this->debugDir = $config_factory->get('acq_commerce.conductor')->get('debug_dir');
  }

  /**
   * Performs full conductor sync.
   */
  public function productFullSync() {
    foreach (acq_commerce_get_store_language_mapping() as $langcode => $store_id) {
      if (empty($store_id)) {
        continue;
      }

      if ($this->debug && !empty($this->debugDir)) {
        // Export product data into file.
        $filename = $this->debugDir . '/products_' . $langcode . '.data';
        $fp = fopen($filename, 'w');
        fclose($fp);
      }

      $endpoint = $this->apiVersion . '/ingest/product/sync?store_id=' . $store_id;

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

}
