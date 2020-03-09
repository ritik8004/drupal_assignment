<?php

namespace Drupal\alshaya_acm\Service;

use Drupal\acq_commerce\APIHelper;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\acq_commerce\Conductor\RouteException;
use Drupal\acq_commerce\Connector\ConnectorException;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\alshaya_acm\Event\AlshayaAcmPlaceOrderFailedEvent;
use Drupal\alshaya_acm\Event\AlshayaAcmUpdateCartFailedEvent;
use Drupal\Core\Site\Settings;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Component\Serialization\Json;

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
   * Alshaya API.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApi;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   * @param \Drupal\acq_commerce\I18nHelper $i18nHelper
   *   I18nHelper object.
   * @param \Drupal\acq_commerce\APIHelper $api_helper
   *   API Helper service object.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api
   *   Alshaya API.
   */
  public function __construct(ClientFactory $client_factory,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactory $logger_factory,
                              I18nHelper $i18nHelper,
                              APIHelper $api_helper,
                              EventDispatcherInterface $dispatcher,
                              AlshayaApiWrapper $alshaya_api) {
    parent::__construct($client_factory, $config_factory, $logger_factory, $i18nHelper, $api_helper, $dispatcher);
    $this->alshayaApi = $alshaya_api;
  }

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
   * Update cart with the new cart array supplied.
   *
   * @param int $cart_id
   *   ID of cart to update.
   * @param object $cart
   *   Cart object to update with.
   *
   * @return array
   *   Full updated cart after submission.
   *
   * @throws \Drupal\acq_commerce\Conductor\RouteException
   *   Failed request exception.
   */
  protected function updateCartDirectCall($cart_id, $cart) {
    $cart_id = (string) $cart_id;

    // Check $item['name'] is a string because in the cart we
    // store name as a 'renderable link object' with a type,
    // a url, and a title. We only want to pass title to the
    // Acquia Commerce Connector.
    // But for robustness we go back to the SKU plugin and ask
    // it to return a name as a string only.
    $items = $cart->items ?? NULL;
    if ($items) {
      foreach ($items as $key => &$item) {
        $cart->items[$key]['qty'] = (int) $item['qty'];

        if (array_key_exists('name', $item)) {
          if (!isset($item['sku'])) {
            $cart->items[$key]['name'] = "";
            continue;
          }

          $sku = SKU::loadFromSku($item['sku']);

          if ($sku instanceof SKU) {
            $plugin = $sku->getPluginInstance();
            $cart->items[$key]['name'] = $plugin->cartName($sku, $item, TRUE);
            continue;
          }

          $cart->items[$key]['name'] = "";
        }
      }
    }

    // Clean up cart data.
    $cart = $this->helper->cleanCart($cart);

    try {
      // First invalidate so even if we get exception, all blocks are updated.
      Cache::invalidateTags(['cart:' . $cart_id]);
      $this->alshayaApi->updateCart($cart_id, Json::decode(Json::encode($cart), TRUE));
    }
    catch (ConnectorException $e) {
      throw new RouteException(__FUNCTION__, $e->getMessage(), $e->getCode(), $this->getRouteEvents());
    }

    return $this->getCart($cart_id);
  }

  /**
   * {@inheritdoc}
   */
  public function updateCart($cart_id, $cart) {
    try {
      if (isset($cart->extension, $cart->extension['do_direct_call'])) {
        $response = $this->updateCartDirectCall($cart_id, $cart);
      }
      else {
        $response = parent::updateCart($cart_id, $cart);
      }
    }
    catch (\Exception $e) {
      $event = new AlshayaAcmUpdateCartFailedEvent($e->getMessage());
      $this->dispatcher->dispatch(AlshayaAcmUpdateCartFailedEvent::EVENT_NAME, $event);
      throw $e;
    }

    return $response ?? NULL;
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

  /**
   * {@inheritdoc}
   */
  public function skuStockCheck($sku) {
    $endpoint = 'stockItems/' . urlencode($sku);
    $response = $this->alshayaApi->invokeApi($endpoint, [], 'GET');

    if (empty($response)) {
      return NULL;
    }

    $response = json_decode($response, TRUE);
    // Add sku to message to allow processing it the same way as stock push.
    $response['sku'] = $sku;

    return $response;
  }

}
