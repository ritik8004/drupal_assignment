<?php

namespace App\Controller\LoyaltyClub;

use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\Aura\OtpHelper;
use App\Service\Aura\SearchHelper;
use App\Service\Aura\AuraErrorCodes;

/**
 * Provides route callbacks for Loyalty Club OTP APIs.
 */
class LoyaltyClubOtpController {
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
   * Magento API Wrapper service.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApiWrapper;

  /**
   * Service for aura otp helper.
   *
   * @var \App\Service\Aura\OtpHelper
   */
  protected $auraOtpHelper;

  /**
   * Service for aura search helper.
   *
   * @var \App\Service\Aura\SearchHelper
   */
  protected $auraSearchHelper;

  /**
   * LoyaltyClubController constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API wrapper service.
   * @param \App\Service\Aura\OtpHelper $aura_otp_helper
   *   Aura otp helper service.
   * @param \App\Service\Aura\SearchHelper $aura_search_helper
   *   Aura search helper service.
   */
  public function __construct(
    LoggerInterface $logger,
    Utility $utility,
    MagentoApiWrapper $magento_api_wrapper,
    OtpHelper $aura_otp_helper,
    SearchHelper $aura_search_helper
  ) {
    $this->logger = $logger;
    $this->utility = $utility;
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->auraOtpHelper = $aura_otp_helper;
    $this->auraSearchHelper = $aura_search_helper;
  }

  /**
   * Send OTP.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return API response status.
   */
  public function sendSignUpOtp(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);
    $mobile = $request_content['mobile'];
    $chosenCountryCode = $request_content['chosenCountryCode'];

    if (empty($chosenCountryCode) || empty($mobile)) {
      $this->logger->error('Error while trying to send otp. Mobile number and Country code is required.');
      return new JsonResponse($this->utility->getErrorResponse('Mobile number and Country code is required.', Response::HTTP_NOT_FOUND));
    }

    // Call search API to check if given mobile number
    // is already registered or not.
    $search_response = $this->auraSearchHelper->search('phone', $chosenCountryCode . $mobile);

    if (!empty($search_response['data']['apc_identifier_number'])) {
      $this->logger->error('Error while trying to send otp. Mobile number @mobile is already registered.', [
        '@mobile' => $mobile,
      ]);
      return new JsonResponse($this->utility->getErrorResponse(AuraErrorCodes::MOBILE_ALREADY_REGISTERED_MSG, AuraErrorCodes::MOBILE_ALREADY_REGISTERED_CODE));
    }

    $response_data = $this->auraOtpHelper->sendOtp($mobile);

    return new JsonResponse($response_data);
  }

  /**
   * Verify OTP.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return API response status.
   */
  public function verifyOtp(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);
    $mobile = $request_content['mobile'];
    $otp = $request_content['otp'];

    if (empty($mobile) || empty($otp)) {
      $this->logger->error('Error while trying to verify otp. Mobile number and OTP is required.');
      return new JsonResponse($this->utility->getErrorResponse('Mobile number and OTP is required.', Response::HTTP_NOT_FOUND));
    }

    try {
      $endpoint = sprintf('/verifyotp/phonenumber/%s/otp/%s', $mobile, $otp);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'status' => $response,
      ];
      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to verify otp for mobile number @mobile. OTP: @otp. Message: @message', [
        '@mobile' => $mobile,
        '@otp' => $otp,
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

  /**
   * Send Link card OTP.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return API response status.
   */
  public function sendLinkCardOtp(Request $request) {
    $response_data = [];
    $request_content = json_decode($request->getContent(), TRUE);

    if (!empty($request_content['type'])) {
      $search_response = $this->auraSearchHelper->searchUserDetails($request_content);

      if (!empty($search_response['error'])) {
        $this->logger->error('Error while trying to search mobile number to send link card OTP. Request Data: @data', [
          '@data' => json_encode($request_content),
        ]);
        return new JsonResponse($search_response);
      }

      if (empty($search_response['data']['mobile'])) {
        $this->logger->error('Error while trying to send link card OTP. Mobile number not found. Request Data: @data', [
          '@data' => json_encode($request_content),
        ]);
        return new JsonResponse($this->utility->getErrorResponse(AuraErrorCodes::NO_MOBILE_FOUND_MSG, Response::HTTP_NOT_FOUND));
      }

      $response_data = $this->auraOtpHelper->sendOtp($search_response['data']['mobile']);

      if (!empty($response_data['status'])) {
        $response_data['mobile'] = $search_response['data']['mobile'];
        $response_data['cardNumber'] = $search_response['data']['apc_identifier_number'];
      }
    }

    return new JsonResponse($response_data);
  }

}
