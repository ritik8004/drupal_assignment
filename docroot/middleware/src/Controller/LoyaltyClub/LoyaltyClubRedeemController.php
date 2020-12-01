<?php

namespace App\Controller\LoyaltyClub;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Cart;

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
   */
  public function __construct(
    MagentoApiWrapper $magento_api_wrapper,
    LoggerInterface $logger,
    Utility $utility,
    Cart $cart
  ) {
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->logger = $logger;
    $this->utility = $utility;
    $this->cart = $cart;
  }

  /**
   * Redeem loyalty points.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return success/failure response.
   */
  public function redeemPoints(Request $request) {
    $cart_id = $this->cart->getCartId();
    if (!$cart_id) {
      $message = 'Error while trying to redeem aura points. Cart is not available for the user.';
      $this->logger->error($message);
      return new JsonResponse($this->utility->getErrorResponse($message, Response::HTTP_NOT_FOUND));
    }

    $request_content = json_decode($request->getContent(), TRUE);
    $data = [
      'redeemAuraPoints' => [
        'action' => 'set points',
        'quote_id' => $cart_id ?? '',
        'redeem_points' => $request_content['redeemPoints'] ?? '',
        'converted_money_value' => $request_content['moneyValue'] ?? '',
        'currencyCode' => $request_content['currencyCode'] ?? '',
        'payment_method' => 'aura_payment',
      ],
    ];

    // Check if required data is present in request.
    if (empty($data['redeemAuraPoints']['redeem_points'])
      || empty($data['redeemAuraPoints']['converted_money_value'])
      || empty($data['redeemAuraPoints']['currencyCode'])
      || empty($request_content['cardNumber'])
      || empty($request_content['userId'])) {
      $message = 'Error while trying to redeem aura points. Redeem Points, Converted Money Value, Currency Code, Card Number and User Id is required.';
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

      // API Call to redeem points.
      $url = sprintf('apc/%d/redeem-points', $request_content['cardNumber']);
      // $response = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);
      $responseData = [];

      // @TODO: Remove hard coded response once API starts working.
      if (!empty($response)) {
        $responseData = [
          'status' => TRUE,
          'data' => [
            'base_grand_total' => $response['base_grand_total'] ?? '',
            'discount_amount' => $response['discount_amount'] ?? '',
            'shipping_incl_tax' => $response['shipping_incl_tax'] ?? '',
            'subtotal_incl_tax' => $response['subtotal_incl_tax'] ?? '',
          ],
        ];
      }

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
