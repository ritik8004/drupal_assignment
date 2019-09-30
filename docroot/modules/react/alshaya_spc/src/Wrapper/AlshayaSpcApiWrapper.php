<?php

namespace Drupal\alshaya_spc\Wrapper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Serialization\Json;

/**
 * Class AlshayaSpcApiWrapper.
 */
class AlshayaSpcApiWrapper {

  /**
   * MDC native endpoint for getting cart.
   */
  const CART_GET_MDC_ENDPOINT = 'carts/%d';

  /**
   * Alshaya api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApiWrapper;

  /**
   * AlshayaSpcApiWrapper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api_wrapper
   *   Alshaya api wrapper.
   */
  public function __construct(AlshayaApiWrapper $alshaya_api_wrapper) {
    $this->alshayaApiWrapper = $alshaya_api_wrapper;
  }

  /**
   * Prepare cart response data here.
   *
   * @param array $data
   *   Data array.
   *
   * @return array
   *   Data array.
   */
  public function prepareCartResponse(array $data = []) {
    if (empty($data)) {
      return $data;
    }

    return $data;
  }

  /**
   * Fetch cart from the MDC.
   *
   * @param int $cart_id
   *   Cart id.
   *
   * @return mixed
   *   Cart data.
   */
  public function getCart(int $cart_id) {
    // Get cart data from MDC.
    $response = $this->alshayaApiWrapper->invokeApi(sprintf(self::CART_GET_MDC_ENDPOINT, $cart_id), [], 'GET');
    $cart = Json::decode($response);
    return $cart;
  }

}
