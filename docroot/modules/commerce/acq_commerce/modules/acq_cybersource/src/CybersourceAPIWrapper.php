<?php

namespace Drupal\acq_cybersource;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\acq_commerce\Conductor\ConductorException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * CybersourceAPIWrapper class.
 */
class CybersourceAPIWrapper extends APIWrapper {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Http\ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   LanguageManagerInterface object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(ClientFactory $client_factory, ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, LoggerChannelFactory $logger_factory) {
    parent::__construct($client_factory, $config_factory, $language_manager, $logger_factory);
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
      return ($client->get($endpoint, $opt));
    };

    try {
      return $this->tryAgentRequest($doReq, 'cybersourceTokenRequest', 'token');
    }
    catch (ConductorException $e) {
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

    $doReq = function ($client, $opt) use ($endpoint, $cart) {
      $opt['json'] = $cart;

      return ($client->post($endpoint, $opt));
    };

    try {
      return $this->tryAgentRequest($doReq, 'updateCart');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }
  }

}
