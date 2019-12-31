<?php

namespace Drupal\acq_cybersource;

use Drupal\acq_commerce\APIHelper;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\acq_commerce\Conductor\RouteException;
use Drupal\acq_commerce\Connector\ConnectorException;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * CybersourceAPIWrapper class.
 */
class CybersourceAPIWrapper extends APIWrapper {

  /**
   * API Helper service object.
   *
   * @var \Drupal\acq_commerce\APIHelper
   */
  protected $helper;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\acq_commerce\APIHelper $api_helper
   *   API Helper service object.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   */
  public function __construct(ClientFactory $client_factory,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactory $logger_factory,
                              I18nHelper $i18n_helper,
                              APIHelper $api_helper,
                              EventDispatcherInterface $dispatcher) {
    parent::__construct($client_factory, $config_factory, $logger_factory, $i18n_helper, $api_helper, $dispatcher);
    // To avoid issues in merging ACM code, not changing from private to
    // protected in base class.
    $this->helper = $api_helper;
    $this->logger = $logger_factory->get('acq_cybersource');
  }

  /**
   * Gets the token from Magento.
   *
   * @param int $cart_id
   *   Cart id.
   * @param string $card_type
   *   Credit card type.
   *
   * @return mixed
   *   API response containing all the data to be passed on to Cybersource.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function cybersourceTokenRequest($cart_id, $card_type) {
    $endpoint = $this->apiVersion . '/agent/cart/token/cybersource';

    $doReq = function ($client, $opt) use ($endpoint, $cart_id, $card_type) {
      $opt['query']['cart_id'] = $cart_id;
      $opt['query']['card_type'] = $card_type;

      // To allow hmac sign to be verified properly we need them in asc order.
      ksort($opt['query']);

      return ($client->get($endpoint, $opt));
    };

    try {
      return $this->tryAgentRequest($doReq, 'cybersourceTokenRequest', 'token');
    }
    catch (ConnectorException $e) {
      $this->logger->warning('Error occurred while getting cybersource token for cart id: %cart_id and card type: %card_type: %message', [
        '%cart_id' => $cart_id,
        '%card_type' => $card_type,
        '%message' => $e->getMessage(),
      ]);

      throw new \Exception($e->getMessage(), $e->getCode());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateCart($cart_id, $cart) {
    $endpoint = $this->apiVersion . "/agent/cart/$cart_id";

    // Check $item['name'] is a string because in the cart we
    // store name as a 'renderable link object' with a type,
    // a url, and a title. We only want to pass title to the
    // Acquia Commerce Connector.
    // But for robustness we go back to the SKU plugin and ask
    // it to return a name as a string only.
    $originalItemsNames = [];
    $items = $cart->items;
    if ($items) {
      foreach ($items as $key => &$item) {
        if (array_key_exists('name', $item)) {
          $originalItemsNames[$key] = $item['name'];

          if (!isset($item['sku'])) {
            $cart->items[$key]['name'] = "";
            continue;
          }

          $sku = SKU::loadFromSku($item['sku']);
          $plugin = $sku->getPluginInstance();

          if (empty($sku) || empty($plugin)) {
            $cart->items[$key]['name'] = "";
            continue;
          }

          $cart->items[$key]['name'] = $plugin->cartName($sku, $item, TRUE);
        }
      }
    }

    $cart = $this->helper->cleanCart($cart);

    $doReq = function ($client, $opt) use ($endpoint, $cart) {
      $opt['json'] = $cart;
      return ($client->post($endpoint, $opt));
    };

    try {
      Cache::invalidateTags(['cart:' . $cart_id]);
      $response = $this->tryAgentRequest($doReq, 'updateCart');
    }
    catch (ConnectorException $e) {
      // Restore cart structure.
      if ($items) {
        foreach ($items as $key => &$item) {
          if (array_key_exists('name', $item)) {
            $cart->items[$key]['name'] = $originalItemsNames[$key];
          }
        }
      }

      // Now throw.
      throw new RouteException(__FUNCTION__, $e->getMessage(), $e->getCode(), $this->getRouteEvents());
    }

    return $response;
  }

}
