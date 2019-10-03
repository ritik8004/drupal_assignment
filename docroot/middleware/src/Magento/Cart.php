<?php

namespace AlshayaMiddleware\Magento;

/**
 * Class Cart.
 */
class Cart {

  /**
   * Magento service.
   *
   * @var MagentoInfo
   */
  protected $magentoInfo;

  /**
   * Cart constructor.
   *
   * @param MagentoInfo $magentoInfo
   *   Magento info service.
   */
  public function __construct(MagentoInfo $magentoInfo) {
    $this->magentoInfo = $magentoInfo;
  }

  /**
   * Get cart by cart id.
   *
   * @param int $cart_id
   *   Cart id.
   *
   * @return array
   *   Cart data.
   */
  public function getCart(int $cart_id) {
    $client = $this->magentoInfo->getMagentoApiClient();
    $url = $this->magentoInfo->getMagentoUrl() . '/' . sprintf('carts/%d/getCart', $cart_id);

    try {
      $response = $client->request('GET', $url);
      $result = $response->getBody()->getContents();
      $cart = json_decode($result, TRUE);

      // In case magento not returns cart.
      if (!$cart || !isset($cart['cart'])) {
        throw new \Exception('Invalid cart id', 500);
      }

      return $cart;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
    }

  }

  /**
   * Add/Update/Remove item in cart.
   *
   * @param int $cart_id
   *   Cart id.
   * @param string $sku
   *   Sku.
   * @param int|null $quantity
   *   Quantity.
   * @param string $action
   *   Action to be performed (add/update/remove).
   *
   * @return array
   *   Cart data.
   */
  public function addUpdateRemoveItem(int $cart_id, string $sku, ?int $quantity, string $action) {
    $data['items'][] = (object) [
      'sku' => $sku,
      'qty' => $quantity ?? 1,
      'quote_id' => $cart_id,
    ];
    $data['extension'] = (object) [
      'action' => $action,
    ];

    return $this->updateCart($data, $cart_id);
  }

  /**
   * Apply promo on the cart.
   *
   * @param int $cart_id
   *   Cart id.
   * @param string|null $promo
   *   Promo to apply.
   * @param string $action
   *   Action to perform (promo apply/remove).
   *
   * @return array
   *   Cart data.
   */
  public function applyRemovePromo(int $cart_id, ?string $promo, string $action) {
    $data = [
      'extension' => (object) [
        'action' => $action,
      ],
    ];

    if ($promo) {
      $data['coupon'] = $promo;
    }

    return $this->updateCart($data, $cart_id);
  }

  /**
   * Common function for updating cart.
   *
   * @param array $data
   *   Data to update for cart.
   * @param int $cart_id
   *   Cart id.
   *
   * @return array
   *   Cart data.
   */
  public function updateCart(array $data, int $cart_id) {
    $client = $this->magentoInfo->getMagentoApiClient();
    $url = $this->magentoInfo->getMagentoUrl() . '/' . sprintf('carts/%d/updateCart', $cart_id);

    try {
      $response = $client->request('POST', $url, ['json' => $data]);
      $result = $response->getBody()->getContents();
      $cart = json_decode($result, TRUE);

      // In case magento not returns cart data.
      if (!$cart || !isset($cart['cart'])) {
        throw new \Exception('Invalid cart', '500');
      }

      return $cart;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Method for error response.
   *
   * @param string $message
   *   Error message.
   * @param string $code
   *   Error code.
   *
   * @return array
   *   Error response array.
   */
  public function getErrorResponse(string $message, string $code) {
    return [
      'error' => TRUE,
      'error_message' => $message,
      'error_code' => $code,
    ];
  }

}
