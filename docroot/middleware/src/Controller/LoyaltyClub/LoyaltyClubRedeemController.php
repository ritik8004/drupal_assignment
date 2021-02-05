<?php

namespace App\Controller\LoyaltyClub;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Aura\RedemptionHelper;
use App\Service\Orders;

/**
 * Provides route callbacks for different Loyalty Club requirements.
 */
class LoyaltyClubRedeemController {

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
   * Service for cart interaction.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * RedemptionHelper service.
   *
   * @var \App\Service\Aura\RedemptionHelper
   */
  protected $redemptionHelper;

  /**
   * Orders service.
   *
   * @var \App\Service\Orders
   */
  protected $orders;

  /**
   * LoyaltyClubRedeemController constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API wrapper service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\Cart $cart
   *   Cart service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Aura\RedemptionHelper $redemption_helper
   *   Drupal service.
   * @param \App\Service\Orders $orders
   *   Orders service.
   */
  public function __construct(
      MagentoApiWrapper $magento_api_wrapper,
      LoggerInterface $logger,
      Utility $utility,
      Cart $cart,
      Drupal $drupal,
      RedemptionHelper $redemption_helper,
      Orders $orders
    ) {
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->logger = $logger;
    $this->utility = $utility;
    $this->cart = $cart;
    $this->drupal = $drupal;
    $this->redemptionHelper = $redemption_helper;
    $this->orders = $orders;
  }

  /**
   * Redeem loyalty points.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return success/failure response.
   */
  public function processRedemption(Request $request) {
    $responseData = [];
    $request_content = json_decode($request->getContent(), TRUE);

    $cart_id = $this->cart->getCartId();
    if (!$cart_id) {
      $message = 'Error while trying to redeem aura points. Cart is not available for the user.';
      $this->logger->error($message);
      return new JsonResponse($this->utility->getErrorResponse($message, Response::HTTP_NOT_FOUND));
    }

    // Check if required data is present in request.
    if (empty($request_content['cardNumber'])
      || empty($request_content['userId'])) {
      $message = 'Error while trying to redeem aura points. Card Number and User Id is required.';
      $this->logger->error($message . 'Data: @request_data', [
        '@request_data' => json_encode($data),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($message, Response::HTTP_NOT_FOUND));
    }

    try {
      // Get user details from session.
      $user = $this->drupal->getSessionCustomerInfo();

      // Check if we have user in session.
      if (empty($user)) {
        $this->logger->error('Error while trying to redeem aura points. No user available in session. User id from request: @uid.', [
          '@uid' => $request_content['userId'],
        ]);
        return new JsonResponse($this->utility->getErrorResponse('No user available in session', Response::HTTP_NOT_FOUND));
      }

      // Check if uid in the request matches the one in session.
      if ($user['uid'] !== $request_content['userId']) {
        $this->logger->error("Error while trying to redeem aura points. User id in request doesn't match the one in session. User id from request: @req_uid. User id in session: @session_uid.", [
          '@req_uid' => $request_content['userId'],
          '@session_uid' => $user['uid'],
        ]);
        return new JsonResponse($this->utility->getErrorResponse("User id in request doesn't match the one in session.", Response::HTTP_NOT_FOUND));
      }

      $redeemPointsRequestData = $this->redemptionHelper->prepareRedeemPointsData($request_content, $cart_id);

      if (empty($redeemPointsRequestData) || !empty($redeemPointsRequestData['error'])) {
        $this->logger->error('Error while trying to create redeem points request data. Request data: @request_data.', [
          '@request_data' => json_encode($redeemPointsRequestData),
        ]);
        return new JsonResponse($redeemPointsRequestData);
      }

      // API call to redeem points.
      $responseData = $this->redemptionHelper->redeemPoints($request_content['cardNumber'], $redeemPointsRequestData);

      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to redeem aura points. Request Data: @request_data. Message: @message', [
        '@request_data' => json_encode($data),
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
