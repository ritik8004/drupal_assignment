<?php

namespace Drupal\alshaya_acm\Service;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Conductor\RouteException;
use Drupal\acq_commerce\Connector\ConnectorException;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\alshaya_acm\Event\AlshayaAcmPlaceOrderFailedEvent;
use Drupal\alshaya_acm\Event\AlshayaAcmUpdateCartFailedEvent;
use Drupal\Core\Site\Settings;

/**
 * Class AlshayaAcmApiWrapper.
 *
 * @package Drupal\alshaya_acm\Service
 */
class AlshayaAcmApiWrapper extends APIWrapper {

  /**
   * Flag to allow disabling API calls.
   *
   * @var bool
   */
  public static $invokeApi = TRUE;

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
  public function getLinkedskus($sku, $type = AcqSkuLinkedSku::LINKED_SKU_TYPE_ALL) {
    // We allow disabling api calls for some cases.
    if (empty(static::$invokeApi)) {
      return [];
    }

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

  /**
   * {@inheritdoc}
   */
  public function getPromotions($type = 'category') {
    // As the parameter is used in endpoint path, we restrict it to avoid
    // unexpected exception.
    if (!in_array($type, ['category', 'cart'])) {
      return [];
    }

    // Increase memory limit for promotions sync as we have a lot of promotions
    // now on production.
    ini_set('memory_limit', '1024M');

    $endpoint = $this->apiVersion . "/agent/promotions/$type";

    $doReq = function ($client, $opt) use ($endpoint) {
      $opt['timeout'] = (int) Settings::get('promotions_sync_timeout', 180);
      return ($client->get($endpoint, $opt));
    };

    $result = [];

    try {
      $result = $this->tryAgentRequest($doReq, 'getPromotions', 'promotions');
    }
    catch (ConnectorException $e) {
      throw new RouteException(__FUNCTION__, $e->getMessage(), $e->getCode(), $this->getRouteEvents());
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function updateCart($cart_id, $cart) {
    try {
      return parent::updateCart($cart_id, $cart);
    }
    catch (\Exception $e) {
      $event = new AlshayaAcmUpdateCartFailedEvent($e->getMessage());
      $this->dispatcher->dispatch(AlshayaAcmUpdateCartFailedEvent::EVENT_NAME, $event);
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function placeOrder($cart_id, $customer_id = NULL) {
    try {
      return parent::placeOrder($cart_id, $customer_id);
    }
    catch (\Exception $e) {
      $event = new AlshayaAcmPlaceOrderFailedEvent($e->getMessage());
      $this->dispatcher->dispatch(AlshayaAcmPlaceOrderFailedEvent::EVENT_NAME, $event);
      throw $e;
    }
  }

}
