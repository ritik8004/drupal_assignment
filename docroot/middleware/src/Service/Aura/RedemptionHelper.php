<?php

namespace App\Service\Aura;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

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
      $response = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);
      $responseData = [];

      if (!empty($response)) {
        $responseData = [
          'status' => TRUE,
          'data' => [
            'paidWithAura' => $response['redeem_response']['cashback_deducted_value'] ?? 0,
            'balancePayable' => $response['redeem_response']['balance_payable'] ?? 0,
            'balancePoints' => $response['redeem_response']['house_hold_balance'] ?? 0,
          ],
        ];
      }
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

  /**
   * Prepare data based on action for redeem points.
   *
   * @return array
   *   Array of data/ error message.
   */
  public function prepareRedeemPointsData($data, $cart_id) {
    $processed_data = [];
    if (!empty($data['action']) && $data['action'] === 'remove points') {
      $processed_data = [
        'redeemPoints' => [
          'action' => 'remove points',
          'quote_id' => $cart_id ?? '',
        ],
      ];
      return $processed_data;
    }

    if (!empty($data['action']) && $data['action'] === 'set points') {
      $processed_data = [
        'redeemPoints' => [
          'action' => 'set points',
          'quote_id' => $cart_id ?? '',
          'redeem_points' => $data['redeemPoints'] ?? '',
          'converted_money_value' => $data['moneyValue'] ?? '',
          'currencyCode' => $data['currencyCode'] ?? '',
          'payment_method' => 'aura_payment',
        ],
      ];

      // Check if required data is present in request.
      if (empty($processed_data['redeemPoints']['redeem_points'])
        || empty($processed_data['redeemPoints']['converted_money_value'])
        || empty($processed_data['redeemPoints']['currencyCode'])) {
        $message = 'Error while trying to redeem aura points. Redeem Points, Converted Money Value and Currency Code is required.';
        $this->logger->error($message . 'Data: @request_data', [
          '@request_data' => json_encode($data),
        ]);
        return $this->utility->getErrorResponse($message, Response::HTTP_NOT_FOUND);
      }

      return $processed_data;
    }

    return $processed_data;
  }

}
