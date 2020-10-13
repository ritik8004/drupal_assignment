<?php

namespace App\Controller;

use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Aura\CustomerHelper;
use App\Service\Drupal\Drupal;

/**
 * Provides route callbacks for Loyalty Customer APIs.
 */
class LoyaltyCustomerController {
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
   * Service for aura customer.
   *
   * @var \App\Service\Aura\CustomerHelper
   */
  protected $auraCustomerHelper;

  /**
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * LoyaltyClubController constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\Aura\CustomerHelper $aura_customer_helper
   *   Aura customer helper service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   */
  public function __construct(
    LoggerInterface $logger,
    Utility $utility,
    CustomerHelper $aura_customer_helper,
    Drupal $drupal
  ) {
    $this->logger = $logger;
    $this->utility = $utility;
    $this->auraCustomerHelper = $aura_customer_helper;
    $this->drupal = $drupal;
  }

  /**
   * Returns the loyalty points related data for the current user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The loyalty points related data for the current user or error message.
   */
  public function getCustomerDetails() {
    $sessionCustomerInfo = $this->drupal->getSessionCustomerInfo();
    $customer_id = $sessionCustomerInfo['customer_id'];

    if (empty($customer_id)) {
      $this->logger->error('Error while trying to fetch loyalty points for customer. No customer available in session.');
      return new JsonResponse($this->utility->getErrorResponse('No user in session', Response::HTTP_NOT_FOUND));
    }

    // Call helper to get customer point details.
    $customer_points = $this->auraCustomerHelper->getCustomerPoints($customer_id);

    // If there is any exception/error, return as is with exception message
    // without processing further.
    if (!empty($customer_points['error'])) {
      $this->logger->notice('Error while trying to fetch customer points for user with customer id @customer_id.', [
        '@customer_id' => $customer_id,
      ]);

      return new JsonResponse($customer_points);
    }

    // Call helper to get customer tier details.
    $customer_tier = $this->auraCustomerHelper->getCustomerTier($customer_id);

    // If there is any exception/error, return as is with exception message
    // without processing further.
    if (!empty($customer_tier['error'])) {
      $this->logger->notice('Error while trying to fetch customer tier for user with customer id @customer_id.', [
        '@customer_id' => $customer_id,
      ]);

      return new JsonResponse($customer_tier);
    }

    return new JsonResponse(array_merge($customer_points, $customer_tier));
  }

}
