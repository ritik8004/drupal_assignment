<?php

namespace Drupal\acq_cybersource;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\acq_commerce\Conductor\RouteException;
use Drupal\acq_commerce\Connector\ConnectorException;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * CybersourceAPIWrapper class.
 */
class CybersourceAPIWrapper extends APIWrapper {

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
   */
  public function __construct(ClientFactory $client_factory,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactory $logger_factory,
                              I18nHelper $i18n_helper) {
    parent::__construct($client_factory, $config_factory, $logger_factory, $i18n_helper);
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

    // Check if there's a customer ID and remove it if it's empty.
    if (isset($cart->customer_id) && empty($cart->customer_id)) {
      unset($cart->customer_id);
    }

    // Check if there's a customer email and remove it if it's empty.
    if (isset($cart->customer_email) && empty($cart->customer_email)) {
      unset($cart->customer_email);
    }

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

          $plugin_manager = \Drupal::service('plugin.manager.sku');
          $plugin = $plugin_manager->pluginInstanceFromType($item['product_type']);
          $sku = SKU::loadFromSku($item['sku']);

          if (empty($sku) || empty($plugin)) {
            $cart->items[$key]['name'] = "";
            continue;
          }

          $cart->items[$key]['name'] = $plugin->cartName($sku, $item, TRUE);
        }
      }
    }

    // Cart extensions must always be objects and not arrays.
    // @TODO: Move this normalization to \Drupal\acq_cart\Cart::__construct and \Drupal\acq_cart\Cart::updateCartObject.
    if (isset($cart->carrier)) {
      if (isset($cart->carrier->extension)) {
        if (!is_object($cart->carrier->extension)) {
          $cart->carrier->extension = (object) $cart->carrier->extension;
        }
      }
      elseif (array_key_exists('extension', $cart->carrier)) {
        if (!is_object($cart->carrier['extension'])) {
          $cart->carrier['extension'] = (object) $cart->carrier['extension'];
        }
      }
    }
    else {
      // Removing shipping address if carrier not set.
      unset($cart->shipping);
    }

    // Cart constructor sets cart to any object passed in,
    // circumventing ->setBilling() so trap any wayward extension[] here.
    // @TODO: Move this normalization to \Drupal\acq_cart\Cart::__construct and \Drupal\acq_cart\Cart::updateCartObject.
    if (isset($cart->billing)) {
      if (isset($cart->billing->extension)) {
        if (!is_object($cart->billing->extension)) {
          $cart->billing->extension = (object) $cart->billing->extension;
        }
      }
      elseif (array_key_exists('extension', $cart->billing)) {
        if (!is_object($cart->billing['extension'])) {
          $cart->billing['extension'] = (object) $cart->billing['extension'];
        }
      }
    }
    if (isset($cart->shipping)) {
      if (isset($cart->shipping->extension)) {
        if (!is_object($cart->shipping->extension)) {
          $cart->shipping->extension = (object) $cart->shipping->extension;
        }
      }
      elseif (array_key_exists('extension', $cart->shipping)) {
        if (!is_object($cart->shipping['extension'])) {
          $cart->shipping['extension'] = (object) $cart->shipping['extension'];
        }
      }
    }

    $doReq = function ($client, $opt) use ($endpoint, $cart) {
      $opt['json'] = $cart;
      return ($client->post($endpoint, $opt));
    };

    try {
      $response = $this->tryAgentRequest($doReq, 'updateCart');
      Cache::invalidateTags(['cart:' . $cart_id]);
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
