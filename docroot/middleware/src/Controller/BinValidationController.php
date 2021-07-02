<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\CheckoutCom\BinValidator;

/**
 * BinValidationController Class for card bin validation.
 */
class BinValidationController {
  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Bin Validator.
   *
   * @var \App\Service\CheckoutCom\BinValidator
   */
  protected $binValidator;

  /**
   * BinValidationController constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\CheckoutCom\BinValidator $bin_validator
   *   Bin Validator.
   */
  public function __construct(LoggerInterface $logger,
                              BinValidator $bin_validator) {
    $this->logger = $logger;
    $this->binValidator = $bin_validator;
  }

  /**
   * Bin validation.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Validation status or error.
   */
  public function handleBinValidation(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);

    if (empty($request_content['bin']) || empty($request_content['paymentMethods'])) {
      $this->logger->error('BIN validation failed. Bin number and Payment method is required for validation. Request Data: @data.', [
        '@data' => $request_content,
      ]);

      return new JsonResponse([
        'error' => TRUE,
      ]);
    }

    // Convert payment methods string to array to validate for each of them.
    $payment_methods = explode(',', $request_content['paymentMethods']);

    foreach ($payment_methods ?? [] as $payment_method) {
      // If the given bin number matches with the bins of given payment method
      // then this card belongs to that payment method, so throw an error
      // asking user to use that payment method.
      if ($this->binValidator->binMatchesPaymentMethod($request_content['bin'], $payment_method)) {
        return new JsonResponse([
          'error' => TRUE,
          'error_message' => 'card_bin_validation_error_message_' . $payment_method,
        ]);
      }
    }

    return new JsonResponse([
      'status' => TRUE,
    ]);
  }

}
