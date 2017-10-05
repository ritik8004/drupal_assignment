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
    $this->product_page_size = $config_factory->get('acq_commerce.conductor')->get('product_page_size');
  }

  /**
   * Performs full conductor sync.
   */
  public function productFullSync($store_id, $langcode, $skus = '', $page_size = 0) {
    if ($this->debug && !empty($this->debugDir)) {
      // Export product data into file.
      $filename = $this->debugDir . '/products_' . $langcode . '.data';
      $fp = fopen($filename, 'w');
      fclose($fp);
    }

    $product_page_size = (int) $this->product_page_size;

    if (!empty($page_size) && is_int($page_size)) {
      $product_page_size = (int) $page_size;
    }

    $endpoint = $this->apiVersion . '/ingest/product/sync';

    $doReq = function ($client, $opt) use ($endpoint, $store_id, $skus, $product_page_size) {
      if ($product_page_size > 0) {
        $opt['query']['page_size'] = $product_page_size;
      }

      if (!empty($skus)) {
        $opt['query']['skus'] = $skus;
      }

      $opt['query']['store_id'] = $store_id;

      return $client->post($endpoint, $opt);
    };

    try {
      $this->tryIngestRequest($doReq, 'productFullSync', 'products');
    }
    catch (ConductorException $e) {
    }
  }

}
