<?php

namespace Drupal\alshaya_acm\Service;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Conductor\RouteException;
use Drupal\acq_commerce\Connector\ConnectorException;
use Drupal\Core\Site\Settings;

/**
 * Class AlshayaAcmApiWrapper.
 *
 * @package Drupal\alshaya_acm\Service
 */
class AlshayaAcmApiWrapper extends APIWrapper {

  /**
   * Get linked skus for a given sku by linked type.
   *
   * @param string $sku
   *   The sku id.
   * @param string $type
   *   Linked type. Like - related/crosssell/upsell.
   *
   * @return array|mixed
   *   All linked skus of given type.
   *
   * @throws \Drupal\acq_commerce\Conductor\RouteException
   */
  public function getLinkedskus($sku, $type = LINKED_SKU_TYPE_ALL) {
    $sku = urlencode($sku);
    $endpoint = $this->apiVersion . "/agent/product/$sku/related/$type";

    $doReq = function ($client, $opt) use ($endpoint) {
      // Allow overriding the timeout value from settings.
      $opt['timeout'] = (int) Settings::get('linked_skus_timeout', 2);
      return ($client->get($endpoint, $opt));
    };

    try {
      $result = $this->tryAgentRequest($doReq, 'linkedSkus', 'related');
    }
    catch (ConnectorException $e) {
      throw new RouteException(__FUNCTION__, $e->getMessage(), $e->getCode(), $this->getRouteEvents());
    }

    return $result;
  }

}
