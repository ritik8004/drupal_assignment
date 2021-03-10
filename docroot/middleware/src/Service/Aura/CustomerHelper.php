<?php

namespace App\Service\Aura;

use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Helper to prepare customer data and api calls.
 *
 * @package App\Service\Aura
 */
class CustomerHelper {

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
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * Utility service.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * AuraHelper constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API wrapper service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(
    MagentoApiWrapper $magento_api_wrapper,
    LoggerInterface $logger,
    Drupal $drupal,
    Utility $utility
  ) {
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->logger = $logger;
    $this->drupal = $drupal;
    $this->utility = $utility;
  }

  /**
   * Get Customer Information.
   *
   * @return array
   *   Return customer's loyalty information.
   */
  public function getCustomerInfo($customer_id) {
    try {
      $endpoint = sprintf('/customers/apcCustomerData/%s', $customer_id);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $response_data = [];

      if (is_array($response)) {
        $response_data = [
          'cardNumber' => $response['apc_identifier_number'] ?? '',
          'auraStatus' => $response['apc_link'] ?? '',
          'auraPoints' => $response['apc_points'] ?? 0,
          'phoneNumber' => $response['apc_phone_number'] ?? '',
          'firstName' => $response['apc_first_name'] ?? '',
          'lastName' => $response['apc_last_name'] ?? '',
        ];
      }

      return $response_data;
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch customer information for user with customer id @customer_id. Message: @message.', [
        '@customer_id' => $customer_id,
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Get Customer Points.
   *
   * @return array
   *   Return customer's point details.
   */
  public function getCustomerPoints($customer_id) {
    try {
      $endpoint = sprintf('/customers/apc-points-balance/%s', $customer_id);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $response_data = [];

      if (is_array($response)) {
        $response_data = [
          'customerId' => $response['customer_id'] ?? '',
          'cardNumber' => $response['apc_identifier_number'] ?? '',
          'auraPoints' => $response['apc_points'] ?? 0,
          'auraPointsToExpire' => $response['apc_points_to_expire'] ?? 0,
          'auraPointsExpiryDate' => $response['apc_points_expiry_date'] ?? '',
          'auraOnHoldPoints' => $response['apc_on_hold_points'] ?? 0,
        ];
      }

      return $response_data;
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch loyalty points for user with customer id @customer_id. Message: @message.', [
        '@customer_id' => $customer_id,
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Get Customer Tier.
   *
   * @return array
   *   Return customer's tier.
   */
  public function getCustomerTier($customer_id) {
    try {
      $endpoint = sprintf('/customers/apc-tiers/%s', $customer_id);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $response_data = [];

      if (is_array($response)) {
        $response_data = [
          'tier' => $response['tier_code'] ?? '',
        ];
      }

      return $response_data;
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch tier information for user with customer id @customer_id. Message: @message.', [
        '@customer_id' => $customer_id,
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Prepare data for aura user status update.
   *
   * @return array
   *   Array of data/ error message.
   */
  public function prepareAuraUserStatusUpdateData($data) {
    $processed_data = [];

    if (empty($data['uid']) || empty($data['apcIdentifierId']) || empty($data['link'])) {
      $this->logger->error('Error while trying to prepare data for updating user AURA Status. User Id, AURA Card number and Link value is required. Data: @request_data', [
        '@request_data' => json_encode($data),
      ]);
      return $this->utility->getErrorResponse('User Id, AURA Card number and Link value is required.', Response::HTTP_NOT_FOUND);
    }

    $processed_data = [
      'statusUpdate' => [
        'apcIdentifierId' => $data['apcIdentifierId'],
        'link' => $data['link'],
      ],
    ];

    if (!empty($data['type']) && $data['type'] === 'withOtp') {
      if (empty($data['otp']) || empty($data['phoneNumber'])) {
        $this->logger->error('Error while trying to prepare data for updating user AURA Status. OTP and mobile number is required. Data: @request_data', [
          '@request_data' => json_encode($data),
        ]);
        return $this->utility->getErrorResponse('OTP and mobile number is required.', Response::HTTP_NOT_FOUND);
      }

      $processed_data['statusUpdate']['otp'] = $data['otp'];
      $processed_data['statusUpdate']['phoneNumber'] = str_replace('+', '', $data['phoneNumber']);
    }

    return $processed_data;
  }

  /**
   * Set loyalty card in cart.
   *
   * @return array
   *   API response status.
   */
  public function setLoyaltyCard($data) {
    try {
      $endpoint = 'apc/set-loyalty-card';
      $response = $this->magentoApiWrapper->doRequest('POST', $endpoint, ['json' => $data]);

      $response_data = [
        'status' => $response,
      ];

      return $response_data;
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to set loyalty card in cart. Request Data: @data. Message: @message.', [
        '@data' => $data,
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Get Reward Activity.
   *
   * @return array
   *   Return customer's reward activity.
   */
  public function getRewardActivity($customerId, $fromDate, $toDate, $maxResults, $channel) {
    try {
      // We are always passing `orderField=date:DESC`.
      $endpoint = sprintf('/customers/apcTransactions?customerId=%s&fromDate=%s&toDate=%s&orderField=date:DESC&maxResults=%s&channel=%s',
        $customerId,
        $fromDate,
        $toDate,
        $maxResults,
        $channel
      );
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $response_data = [];

      if (!empty($response['apc_transactions'])) {
        foreach ($response['apc_transactions'] as $key => $transaction) {
          $response_data[$key] = [
            'orderNo' => $transaction['trn_no'],
            'date' => $transaction['date'],
            'orderTotal' => $transaction['total_value'],
            'currencyCode' => $transaction['currency_code'],
            'channel' => $transaction['channel'],
            'auraPoints' => $transaction['points'],
          ];

          if (!empty($transaction['points_balances'][0])) {
            $response_data[$key]['status'] = $transaction['points_balances'][0]['status'];
            $response_data[$key]['statusName'] = $transaction['points_balances'][0]['status_name'];
          }
        }
      }

      return $response_data;
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to get reward activity of the user. Endpoint: @endpoint. Message: @message.', [
        '@endpoint' => $endpoint,
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

}
