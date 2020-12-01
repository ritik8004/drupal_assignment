<?php

namespace App\Service\Aura;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;

/**
 * Helper for aura otp related APIs.
 *
 * @package App\Service\Aura
 */
class RedemptionHelper {

  /**
   * Magento API Wrapper service.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApiWrapper;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Utility service.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * RedemptionHelper constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API wrapper service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(
    MagentoApiWrapper $magento_api_wrapper,
    LoggerInterface $logger,
    Utility $utility
  ) {
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->logger = $logger;
    $this->utility = $utility;
  }

  /**
   * Send OTP.
   *
   * @return array
   *   Return API response/error.
   */
  public function redeemPoints($cardNumber, $data) {
    try {
      // API Call to redeem points.
      $url = sprintf('apc/%d/redeem-points', $cardNumber);
      // $response = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);
      $responseData = [];

      // @todo Remove hard coded response once API starts working.
      // if (!empty($response)) {
      $responseData = [
        'status' => TRUE,
        'data' => [
          'base_grand_total' => $response['base_grand_total'] ?? '',
          'discount_amount' => $response['discount_amount'] ?? '',
          'shipping_incl_tax' => $response['shipping_incl_tax'] ?? '',
          'subtotal_incl_tax' => $response['subtotal_incl_tax'] ?? '',
        ],
      ];
      // }
      return $responseData;
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to redeem aura points. Request Data: @request_data. Message: @message', [
        '@request_data' => json_encode($data),
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

}
