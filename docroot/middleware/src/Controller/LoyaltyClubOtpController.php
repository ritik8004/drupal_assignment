<?php

namespace App\Controller;

use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
   * LoyaltyClubController constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(
    LoggerInterface $logger,
    Utility $utility
  ) {
    $this->logger = $logger;
    $this->utility = $utility;
  }

  /**
   * Send OTP.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return API response status.
   */
  public function sendOtp(Request $request) {
    $mobile = $request->request->get('mobile');

    if (empty($mobile)) {
      $this->logger->error('Error while trying to send otp. Mobile number is required.');
      return new JsonResponse($this->utility->getErrorResponse('Mobile number is required', Response::HTTP_NOT_FOUND));
    }

    try {
      $endpoint = sprintf('/sendotp/phonenumber/%s', $mobile);

      // @TODO: Remove the hardcoded value when MDC API is ready.
      // $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'status' => TRUE,
      ];
      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to send otp on mobile number @mobile. Message: @message', [
        '@mobile' => $mobile,
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

  /**
   * Verify OTP.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return API response status.
   */
  public function verifyOtp(Request $request) {
    $mobile = $request->request->get('mobile');
    $otp = $request->request->get('otp');

    if (empty($mobile) || empty($otp)) {
      $this->logger->error('Error while trying to verify otp. Mobile number and OTP is required.');
      return new JsonResponse($this->utility->getErrorResponse('Mobile number and OTP is required.', Response::HTTP_NOT_FOUND));
    }

    try {
      $endpoint = sprintf('/verifyotp/phonenumber/%s/otp/%s', $mobile, $otp);

      // @TODO: Remove the hardcoded value when MDC API is ready.
      // $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'status' => TRUE,
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

}
