<?php

namespace Drupal\alshaya_acm\Service;

use Drupal\acq_commerce\APIHelper;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\acq_commerce\Conductor\RouteException;
use Drupal\acq_commerce\Connector\ConnectorException;
use Drupal\acq_commerce\Event\OrderPlacedEvent;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\alshaya_acm\Event\AlshayaAcmPlaceOrderFailedEvent;
use Drupal\alshaya_acm\Event\AlshayaAcmUpdateCartFailedEvent;
use Drupal\Core\Site\Settings;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class Alshaya Acm Api Wrapper.
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
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   Lock service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ClientFactory $client_factory,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactory $logger_factory,
                              I18nHelper $i18nHelper,
                              APIHelper $api_helper,
                              EventDispatcherInterface $dispatcher,
                              AlshayaApiWrapper $alshaya_api,
                              LockBackendInterface $lock,
                              ModuleHandlerInterface $module_handler) {
    parent::__construct($client_factory, $config_factory, $logger_factory, $i18nHelper, $api_helper, $dispatcher, $lock, $module_handler);
    $this->alshayaApi = $alshaya_api;
    $this->configFactory = $config_factory;
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
      $this->alshayaApi->updateCart($cart_id, Json::decode(Json::encode($cart)));
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
      if ($this->isDoubleCheckEnabled()) {
        try {
          $cart = self::getCartFromStorage();
          $cartReservedOrderId = $cart->getExtension('real_reserved_order_id');
          $lastOrder = self::getLastOrder((int) $cart->customerId());

          if ($lastOrder && $cartReservedOrderId === $lastOrder['increment_id']) {
            $this->logger->warning('Place order failed but order was placed, we will move forward. Message: @message, Reserved order id: @order_id, Cart id: @cart_id', [
              '@message' => $e->getMessage(),
              '@order_id' => $cartReservedOrderId,
              '@cart_id' => $cart->id(),
            ]);

            $this->dispatcher->dispatch(OrderPlacedEvent::EVENT_NAME, new OrderPlacedEvent($lastOrder, $cart->id()));
            return $lastOrder;
          }
          else {
            $this->logger->warning('Place order failed and we tried to double check but order was not found. Message: @message, Reserved order id: @order_id, Cart id: @cart_id', [
              '@message' => $e->getMessage(),
              '@order_id' => $cartReservedOrderId,
              '@cart_id' => $cart->id(),
            ]);
          }
        }
        catch (\Exception $doubleException) {
          $this->logger->error('Error occurred while trying to double check. Exception: @message', [
            '@message' => $doubleException->getMessage(),
          ]);
        }
      }

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

    $request_options = [
      'timeout' => $this->alshayaApi->getMagentoApiHelper()->getPhpTimeout('stock_get'),
    ];

    $response = $this->alshayaApi->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

    if (empty($response)) {
      return NULL;
    }

    $response = json_decode($response, TRUE);
    // Add sku to message to allow processing it the same way as stock push.
    $response['sku'] = $sku;

    return $response;
  }

  /**
   * Check in config if we need to double check on place order exception.
   *
   * @return bool
   *   TRUE if we need to double check on place order exception.
   */
  private function isDoubleCheckEnabled() {
    static $value = NULL;

    if (!isset($value)) {
      // If not set, we will consider it as TRUE.
      $value = $this->configFactory->get('alshaya_acm_checkout.settings')->get('place_order_double_check_after_exception') ?? TRUE;
    }

    return $value;
  }

  /**
   * Function to get cart from session.
   *
   * We use this as workaround as adding service as dependency is resulting
   * into circular dependency error. Also, we are going to move away from this
   * very very soon in CORE-10070.
   *
   * @return \Drupal\acq_cart\CartInterface
   *   Cart.
   */
  protected static function getCartFromStorage() {
    return \Drupal::service('acq_cart.cart_storage')->getCart(FALSE);
  }

  /**
   * Function to get last order for customer.
   *
   * We use this as workaround as adding service as dependency is resulting
   * into circular dependency error. Also, we are going to move away from this
   * very very soon in CORE-10070.
   *
   * @return array|null
   *   Order data as array if found.
   */
  protected static function getLastOrder(int $customer_id) {
    return \Drupal::service('alshaya_acm_customer.orders_manager')->getLastOrder($customer_id);
  }

}
