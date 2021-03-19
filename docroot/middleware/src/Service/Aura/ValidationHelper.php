<?php

namespace App\Service\Aura;

use App\Service\Utility;
use Psr\Log\LoggerInterface;

/**
 * Helper for aura otp related APIs.
 *
 * @package App\Service\Aura
 */
class ValidationHelper {

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
   * ValidationHelper constructor.
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
   * Validate input data based on type.
   *
   * @return array
   *   Error/empty array.
   */
  public function validateInput($type, $value) {
    if ($type === 'email') {
      if (empty($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $this->logger->error('Email is missing/invalid. Data: @data', [
          '@data' => $value,
        ]);
        return $this->utility->getErrorResponse(AuraErrorCodes::EMPTY_EMAIL, AuraErrorCodes::INVALID_EMAIL);
      }
      return [];
    }

    if ($type === 'cardNumber' || $type === 'apcNumber') {
      if (empty($value) || !preg_match('/^\d+$/', $value)) {
        $this->logger->error('Card number is missing/invalid. Data: @data', [
          '@data' => $value,
        ]);
        return $this->utility->getErrorResponse(AuraErrorCodes::EMPTY_CARD, AuraErrorCodes::INVALID_CARDNUMBER);
      }
      return [];
    }

    if ($type === 'mobile' || $type === 'phone') {
      if (empty($value) || !preg_match('/^\+\d+$/', $value)) {
        $this->logger->error('Mobile number is missing/invalid. Data: @data', [
          '@data' => $value,
        ]);
        return $this->utility->getErrorResponse(AuraErrorCodes::EMPTY_MOBILE, AuraErrorCodes::INVALID_MOBILE);
      }
      return [];
    }

    return [];
  }

}
