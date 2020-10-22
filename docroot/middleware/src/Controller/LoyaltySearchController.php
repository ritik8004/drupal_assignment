<?php

namespace App\Controller;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides route callbacks for Loyalty Search APIs.
 */
class LoyaltySearchController {

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
   * LoyaltySearchController constructor.
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
   * Get APC user details by email.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return users loyalty details.
   */
  public function getApcUserDetailsByEmail(Request $request) {
    try {
      $request_content = json_decode($request->getContent(), TRUE);
      $email = $request_content['email'];

      // Check if required data is present in request.
      if (empty($email)) {
        $this->logger->error('Error while trying to fetch APC user details for user with email address. Email address is required.');
        return new JsonResponse($this->utility->getErrorResponse('Email address is required.', Response::HTTP_NOT_FOUND));
      }

      $endpoint = sprintf('/customers/apc-search/email/%s', $email);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'status' => TRUE,
        'data' => $response,
      ];
      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch APC user details for user with email address @email. Message: @message', [
        '@email' => $email,
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

  /**
   * Get APC user details by card number.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return users loyalty details.
   */
  public function getApcUserDetailsByCard(Request $request) {
    try {
      $request_content = json_decode($request->getContent(), TRUE);
      $cardNumber = $request_content['cardNumber'];

      // Check if required data is present in request.
      if (empty($cardNumber)) {
        $this->logger->error('Error while trying to fetch APC user details for user with card number. Card Number is required.');
        return new JsonResponse($this->utility->getErrorResponse('Card Number is required.', Response::HTTP_NOT_FOUND));
      }

      $endpoint = sprintf('/customers/apc-search/apcNumber/%s', $cardNumber);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'status' => TRUE,
        'data' => $response,
      ];
      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch APC user details for user with card number @cardNumber. Message: @message', [
        '@cardNumber' => $cardNumber,
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

  /**
   * Get APC user details by mobile number.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return users loyalty details.
   */
  public function getApcUserDetailsByMobileNumber(Request $request) {
    try {
      $request_content = json_decode($request->getContent(), TRUE);
      $mobileNumber = $request_content['mobileNumber'];

      // Check if required data is present in request.
      if (empty($mobileNumber)) {
        $this->logger->error('Error while trying to fetch APC user details for user with mobile number. Mobile Number is required.');
        return new JsonResponse($this->utility->getErrorResponse('Mobile Number is required.', Response::HTTP_NOT_FOUND));
      }

      $endpoint = sprintf('/customers/apc-search/phone/%s', $mobileNumber);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'status' => TRUE,
        'data' => $response,
      ];
      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch APC user details for user with mobile number @mobileNumber. Message: @message', [
        '@mobileNumber' => $mobileNumber,
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
