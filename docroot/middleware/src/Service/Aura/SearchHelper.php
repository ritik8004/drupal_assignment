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
   * Validation Helper service.
   *
   * @var \App\Service\Aura\ValidationHelper
   */
  protected $validationHelper;

  /**
   * SearchHelper constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API wrapper service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\Aura\ValidationHelper $validation_helper
   *   Validation Helper service.
   */
  public function __construct(
    MagentoApiWrapper $magento_api_wrapper,
    LoggerInterface $logger,
    Utility $utility,
    ValidationHelper $validation_helper
  ) {
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->logger = $logger;
    $this->utility = $utility;
    $this->validationHelper = $validation_helper;
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
    $validation = $this->validationHelper->validateInput($input['type'], $input['value']);

    if (!empty($validation['error'])) {
      return $validation;
    }

    if ($input['type'] === 'email') {
      // Call search api to get mobile number to send otp.
      $search_response = $this->search('email', $input['value']);

      if (!empty($search_response['error'])) {
        return $this->utility->getErrorResponse(AuraErrorCodes::NO_MOBILE_FOUND_MSG, AuraErrorCodes::INVALID_EMAIL);
      }

      return $search_response;
    }

    if ($input['type'] === 'cardNumber') {
      // Call search api to get mobile number to send otp.
      $search_response = $this->search('apcNumber', $input['value']);

      if (!empty($search_response['error'])) {
        return $this->utility->getErrorResponse(AuraErrorCodes::NO_MOBILE_FOUND_MSG, AuraErrorCodes::INVALID_CARDNUMBER);
      }

      return $search_response;
    }

    if ($input['type'] === 'mobile') {
      // Call search api to verify mobile number to send otp.
      $search_response = $this->search('phone', $input['value']);

      if (!empty($search_response['error'])) {
        return $this->utility->getErrorResponse(AuraErrorCodes::NO_MOBILE_FOUND_MSG, AuraErrorCodes::INVALID_MOBILE);
      }

      return $search_response;
    }

    return [];
  }

}
