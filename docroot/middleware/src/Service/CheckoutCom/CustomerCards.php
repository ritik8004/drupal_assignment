<?php

namespace App\Service\CheckoutCom;

use Psr\Log\LoggerInterface;

/**
 * Class CustomerCards.
 */
class CustomerCards {

  /**
   * Checkout.com Helper.
   *
   * @var \App\Service\CheckoutCom\Helper
   */
  protected $helper;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * APIWrapper constructor.
   *
   * @param \App\Service\CheckoutCom\Helper $helper
   *   Checkout.com Helper.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(
    Helper $helper,
    LoggerInterface $logger
  ) {
    $this->helper = $helper;
    $this->logger = $logger;
  }

  /**
   * Get given card hash info for given customer.
   *
   * @param int $customer_id
   *   The customer id.
   * @param string $card_hash
   *   The card hash.
   *
   * @return mixed|null
   *   Return array of card data or null.
   */
  public function getGivenCardInfo(int $customer_id, string $card_hash) {
    $cards = $this->getCustomerCards($customer_id);
    $decode_hash = $this->deocodePublicHash($card_hash);
    if (isset($cards[$decode_hash])) {
      return $cards[$decode_hash];
    }
    return NULL;
  }

  /**
   * Get customer stored card.
   *
   * @return array
   *   Return array of customer cards or empty array.
   */
  public function getCustomerCards(int $customer_id) {
    $response = $this->helper->getCustomerCards($customer_id);

    if (!empty($response) && isset($response['message'])) {
      $this->logger->error(strtr($response['message'], $response['parameters'] ?? []));
      return [];
    }

    return !empty($response['items'])
      ? $this->extractCardInfo($response['items'])
      : [];
  }

  /**
   * Extract encoded token details of card info.
   *
   * @param array $cards
   *   List of stored cards.
   *
   * @return array
   *   Return process array of card list.
   */
  protected function extractCardInfo(array $cards): array {
    if (empty($cards)) {
      return [];
    }

    $card_list = [];
    foreach ($cards as $card) {
      $token_details = json_decode($card['token_details'], TRUE);
      // @todo: Remove if we are already receiving mada:true/false.
      $token_details['mada'] = isset($token_details['mada']) && $token_details['mada'] == 'Y';
      // Encode public hash.
      // https://github.com/acquia-pso/alshaya/pull/13267#discussion_r311886591.
      $card_list[$card['public_hash']] = array_merge($card, $token_details);
    }
    return $card_list;
  }

  /**
   * Encode tokenised card's public hash.
   *
   * @param string $public_hash
   *   The public hash to encode.
   *
   * @return string
   *   The base64_encode public hash.
   */
  public function encodePublicHash(string $public_hash) {
    return base64_encode($public_hash);
  }

  /**
   * Decode public hash to get original public hash.
   *
   * @param string $public_hash
   *   The base64_encoded public hash.
   *
   * @return string
   *   The base64_decoded public hash.
   */
  public function deocodePublicHash(string $public_hash) {
    return base64_decode($public_hash);
  }

}
