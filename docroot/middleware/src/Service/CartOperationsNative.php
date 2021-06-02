<?php

namespace App\Service;

use App\Service\Magento\MagentoApiWrapper;

/**
 * Contains methods for native Cart operations.
 */
class CartOperationsNative {

  /**
   * Magento API Wrapper.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApiWrapper;

  /**
   * CartOperationsNative constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API Wrapper.
   */
  public function __construct(MagentoApiWrapper $magento_api_wrapper) {
    $this->magentoApiWrapper = $magento_api_wrapper;
  }

  /**
   * Wrapper function for native remove cart item API.
   *
   * @param int $cart_id
   *   Cart ID.
   * @param int $item_id
   *   Cart Item ID to remove.
   *
   * @return array|mixed
   *   API response.
   */
  public function removeItem(int $cart_id, int $item_id) {
    $endpoint = 'carts/{cartId}/items/{itemId}';
    $endpoint = str_replace('{cartId}', $cart_id, $endpoint);
    $endpoint = str_replace('{itemId}', $item_id, $endpoint);
    $request_options = [
      'timeout' => $this->magentoApiWrapper->getMagentoInfo()->getPhpTimeout('cart_remove'),
    ];

    $response = $this->magentoApiWrapper->doRequest(
      'DELETE',
      $endpoint,
      $request_options,
      'native'
    );

    return $response;
  }

  /**
   * Wrapper function for native add/update cart item API.
   *
   * @param int $cart_id
   *   Cart ID.
   * @param array $item
   *   Cart Item array.
   *
   * @return array|mixed
   *   API response.
   */
  public function addUpdateCartItem(int $cart_id, array $item) {
    $endpoint = 'carts/{cartId}/items';
    $endpoint = str_replace('{cartId}', $cart_id, $endpoint);

    $request = [
      'json' => [
        'cart_item' => $item,
      ],
      'timeout' => $this->magentoApiWrapper->getMagentoInfo()->getPhpTimeout('cart_update'),
    ];

    $response = $this->magentoApiWrapper->doRequest(
      'POST',
      $endpoint,
      $request,
      'native'
    );

    return $response;
  }

}
