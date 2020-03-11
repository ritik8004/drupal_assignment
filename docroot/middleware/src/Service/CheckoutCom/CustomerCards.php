<?php

namespace App\Service\CheckoutCom;

use App\Service\Drupal\DrupalInfo;
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
   * Service to get Drupal Info.
   *
   * @var \App\Service\Drupal\DrupalInfo
   */
  protected $drupalInfo;

  /**
   * Mada Validator.
   *
   * @var \App\Service\CheckoutCom\MadaValidator
   */
  protected $madaValidator;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Credit card type map.
   *
   * @var array
   */
  protected $ccTypesMap = [
    'AE' => 'amex',
    'VI' => 'visa',
    'MC' => 'mastercard',
    'DI' => 'discover',
    'JCB' => 'jcb',
    'DN' => 'dinersclub',
  ];

  /**
   * APIWrapper constructor.
   *
   * @param \App\Service\CheckoutCom\Helper $helper
   *   Checkout.com Helper.
   * @param \App\Service\Drupal\DrupalInfo $drupal_info
   *   Service to get Drupal Info.
   * @param \App\Service\CheckoutCom\MadaValidator $mada_validator
   *   Mada Validator.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(
    Helper $helper,
    DrupalInfo $drupal_info,
    MadaValidator $mada_validator,
    LoggerInterface $logger
  ) {
    $this->helper = $helper;
    $this->drupalInfo = $drupal_info;
    $this->madaValidator = $mada_validator;
    $this->logger = $logger;
  }

  /**
   * Get customer stored card.
   *
   * @return array
   *   Return array of customer cards or empty array.
   */
  public function getCustomerCards($customer_id) {

    $response = $this->helper->getCustomerCards($customer_id);

    if (!empty($response) && isset($response['message'])) {
      $this->logger->error(strtr($response['message'], $response['parameters'] ?? []));
      return [];
    }

    $cards = !empty($response['items'])
      ? $this->extractCardInfo($response['items'])
      : [];

    // Sort cards by last saved first.
    $cards = $this->sortCardsByDate($cards);
    return $cards;
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
      $card['paymentMethod'] = $this->getCardType($token_details['type']);
      // @todo: Remove if we are already receiving mada:true/false.
      $token_details['mada'] = isset($token_details['mada']) && $token_details['mada'] == 'Y';
      // Encode public hash.
      // https://github.com/acquia-pso/alshaya/pull/13267#discussion_r311886591.
      $card['public_hash'] = $this->encodePublicHash($card['public_hash']);
      $card_list[$card['public_hash']] = array_merge($card, $token_details);
    }
    return $card_list;
  }

  /**
   * Get card type based on code.
   *
   * @param string $type
   *   Card type code.
   *
   * @return string|null
   *   Return card type name or null.
   */
  public function getCardType($type): ?string {
    return $this->ccTypesMap[$type] ?? NULL;
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

  /**
   * Sort cards by last saved dates first.
   *
   * @param array $cards
   *   The array of saved cards.
   *
   * @return array
   *   Return sorted array of cards.
   */
  protected function sortCardsByDate(array $cards): array {
    uasort($cards, function ($a, $b) {
      return (strtotime($a['created_at']) > strtotime($b['created_at'])) ? -1 : 1;
    });
    return $cards;
  }

}
