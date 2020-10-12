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

      // @TODO: Update this when we have API ready.
      // $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      return new JsonResponse([
        'points' => '20000',
        'expiredPoints' => '100',
        'expiredPointsDate' => '2021-06-05',
      ]);

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
  public function getApcUserDetailsByEmail() {
    try {
      // Get user email from session.
      $user = $this->drupal->getSessionCustomerInfo();
      $endpoint = sprintf('/customers/apc-search/email/%s', $user['email']);

      // @TODO: Remove the hardcoded value when MDC API is ready.
      // $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'apcCard' => '6362544000099511',
      ];
      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch APC user details for user with email address @email. Message: @message', [
        '@email' => $user['email'],
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

  /**
   * Update User's AURA Status.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return success/failure response.
   */
  public function apcStatusUpdate(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);
    $uid = $request_content['uid'];
    $updated_aura_status = $request_content['updatedAuraStatus'];
    $data['apcIdentifierId'] = $request_content['apcIdentifierId'];
    $data['apcLinkStatus'] = $request_content['apcLinkStatus'];
    $data['link'] = $request_content['link'];

    // Check if required data is present in request.
    if (empty($data['apcIdentifierId']) || $data['apcLinkStatus'] === '' || empty($data['link'])) {
      $this->logger->error('Error while trying to update user AURA Status. AURA Card number, Status and Link value is required. Data: @request_data', [
        '@request_data' => json_encode($data),
      ]);
      return new JsonResponse($this->utility->getErrorResponse('AURA Card number, Status and Link value is required.', Response::HTTP_NOT_FOUND));
    }

    try {
      // Get user details from session.
      $user = $this->drupal->getSessionCustomerInfo();

      // Check if we have user in session.
      if (empty($user)) {
        $this->logger->error('Error while trying to update user AURA Status. No user available in session. User id from request: @uid.', [
          '@uid' => $uid,
        ]);
        return new JsonResponse($this->utility->getErrorResponse('No user available in session', Response::HTTP_NOT_FOUND));
      }

      // Check if uid in the request matches the one in session.
      if ($user['uid'] !== $uid) {
        $this->logger->error("Error while trying to update user AURA Status. User id in request doesn't match the one in session. User id from request: @req_uid. User id in session: @session_uid.", [
          '@req_uid' => $uid,
          '@session_uid' => $user['uid'],
        ]);
        return new JsonResponse($this->utility->getErrorResponse("User id in request doesn't match the one in session.", Response::HTTP_NOT_FOUND));
      }

      $data['customerId'] = $user['customer_id'];

      $url = 'customers/apc-status-update';

      // @TODO: Update this when MDC API is ready.
      // $response = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);
      // On API success, update the user AURA Status in Drupal.
      $updated = $this->drupal->updateUserAuraStatus($uid, $updated_aura_status);

      // Check if user aura status was updated successfully in drupal.
      if (!$updated) {
        $message = 'Error while trying to update user AURA Status field in Drupal.';
        $this->logger->error($message . ' User Id: @uid, Customer Id: @customer_id, Aura Status: @aura_status.', [
          '@uid' => $uid,
          '@aura_status' => $data['apcLinkStatus'],
          '@customer_id' => $user['customer_id'],
        ]);
        return new JsonResponse($this->utility->getErrorResponse($message, 500));
      }

      $response = [
        'status' => TRUE,
      ];

      return new JsonResponse($response);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to update AURA Status for user with customer id @customer_id. Message: @message', [
        '@customer_id' => $user['customer_id'],
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
