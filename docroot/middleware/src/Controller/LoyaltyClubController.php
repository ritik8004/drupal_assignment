<?php

namespace App\Controller;

use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides route callbacks for different Loyalty Club requirements.
 */
class LoyaltyClubController {

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
   * Service for cart interaction.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * LoyaltyClubController constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API wrapper service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\Cart $cart
   *   Cart service.
   */
  public function __construct(
    MagentoApiWrapper $magento_api_wrapper,
    LoggerInterface $logger,
    Drupal $drupal,
    Utility $utility,
    Cart $cart
  ) {
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->logger = $logger;
    $this->drupal = $drupal;
    $this->utility = $utility;
    $this->cart = $cart;
  }

  /**
   * Returns the loyalty points related data for the current user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The loyalty points related data for the current user or error message.
   */
  public function getCustomerPoints() {
    $customer_id = $this->drupal->getSessionCustomerInfo()['customer_id'];

    if (empty($customer_id)) {
      $this->logger->error('Error while trying to fetch loyalty points for customer. No customer available in session.');
      return new JsonResponse($this->utility->getErrorResponse('No user in session', Response::HTTP_NOT_FOUND));
    }

    try {
      $endpoint = sprintf('/customers/apc-points-balance/%s', $customer_id);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);

      return new JsonResponse([
        'points' => $response['apcPoints'],
        'expiredPoints' => $response['apcExpiredPoints'],
        'expiredPointsDate' => $response['apcExpiredPointsDate'],
      ]);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch loyalty points for user with customer id @customer_id.', [
        '@customer_id' => $customer_id,
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

  /**
   * Returns the loyalty points related data for a product.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response having the key 'apcPoints' for points.
   */
  public function getProductPoints(Request $request) {
    // Expecting 'items' array in the request body with each item having
    // 'sku', 'quantity'(int) and 'price'(int).
    $items = $request->request->get('items');
    if (empty($items)) {
      return new JsonResponse($this->utility->getErrorResponse('No items found in the request body.', Response::HTTP_NOT_FOUND));
    }

    $cart = $this->cart->getCart();
    if (empty($cart)) {
      return new JsonResponse($this->utility->getErrorResponse('Cart not found for the user.', Response::HTTP_NOT_FOUND));
    }

    // Hard coded return value for now since MDC not available.
    return new JsonResponse(['apcPoints' => '2000']);

    // $user_identifier_number = 10;
    // $endpoint = sprintf('/apc/%s/sales', $user_identifier_number);
    // try {
    // $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
    // return new JsonResponse($response['apcPoints']);
    // }
    // catch (\Exception $e) {
    // $customer_id = $this->drupal->getSessionCustomerInfo()['customer_id'];
    // $this->logger->notice('Error while trying to fetch product points for
    // user with customer id @customer_id.', [
    // '@customer_id' => $customer_id,
    // ]);
    // return new JsonResponse('Error while trying to fetch product points',
    // Response::HTTP_INTERNAL_SERVER_ERROR);
    // }
  }

  /**
   * Get APC user details by email.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return card number of the user.
   */
  public function getApcUserDetailsByEmail(Request $request) {
    try {
      // Get user email from session.
      $user = $this->drupal->getSessionCustomerInfo();
      if (empty($user['email'])) {
        $this->logger->error('Error while trying to fetch APC user details by email. Email is required.');
        throw new \Exception('Email is required.');
      }

      $endpoint = sprintf('/customers/apc-search/email/%s', $user['email']);

      // @TODO: Remove the hardcoded value when MDC API is ready.
      // $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'apcCard' => '1234567890',
      ];
      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch APC user details for user with email address @email.', [
        '@email' => $user['email'],
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
