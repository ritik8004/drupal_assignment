<?php

namespace App\Service\Aura;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;

/**
 * Helper for aura search related APIs.
 *
 * @package App\Service\Aura
 */
class SearchHelper {

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
   * SearchHelper constructor.
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
   * Search.
   *
   * @return array
   *   Return API response/error.
   */
  public function search($type, $value) {
    try {
      $endpoint = sprintf('/customers/apc-search/%s/%s', $type, $value);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'status' => TRUE,
        'data' => $response,
      ];
      return $responseData;
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to search APC user. Endpoint: @endpoint. Message: @message', [
        '@endpoint' => $endpoint,
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Search based on type of input to get user details.
   *
   * @return array
   *   Return API response/error.
   */
  public function searchUserDetails($input) {
    if ($input['type'] === 'email') {
      if (empty($input['value']) || !filter_var($input['value'], FILTER_VALIDATE_EMAIL)) {
        $this->logger->error('Error while trying search user details. Email is missing/invalid. Data: @data', [
          '@data' => json_encode($input),
        ]);
        return $this->utility->getErrorResponse('INVALID_EMAIL', 500);
      }
      // Call search api to get mobile number to send otp.
      $search_response = $this->search('email', $input['value']);

      return $search_response;
    }

    if ($input['type'] === 'cardNumber') {
      if (empty($input['value']) || !preg_match('/^\d+$/', $input['value'])) {
        $this->logger->error('Error while trying search user details. Card number is missing/invalid. Data: @data', [
          '@data' => json_encode($input),
        ]);
        return $this->utility->getErrorResponse('INVALID_CARDNUMBER', 500);
      }
      // Call search api to get mobile number to send otp.
      $search_response = $this->search('apcNumber', $input['value']);

      return $search_response;
    }

    if ($input['type'] === 'mobile') {
      if (empty($input['value']) || !preg_match('/^\d+$/', $input['value'])) {
        $this->logger->error('Error while trying search user details. Card number is missing/invalid. Data: @data', [
          '@data' => json_encode($input),
        ]);
        return $this->utility->getErrorResponse('INVALID_MOBILE_ERROR', 500);
      }
      // Call search api to verify mobile number to send otp.
      $search_response = $this->search('phone', $input['value']);

      return $search_response;
    }

    return [];
  }

}
