<?php

namespace App\Controller\LoyaltyClub;

use App\Service\Drupal\Drupal;
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
class LoyaltyClubProgressTracker {

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
   * LoyaltyClubProgressTracker constructor.
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
   * Get progress tracker.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return success/failure response.
   */
  public function getProgressTracker(Request $request) {
    try {
      $request_uid = $request->query->get('uid');
      // Get user details from session.
      $customer_id = $this->cart->getDrupalInfo('customer_id');
      $uid = $this->cart->getDrupalInfo('uid');

      // Check if we have user in session.
      if (empty($customer_id) || empty($uid)) {
        $this->logger->error('Error while trying to get progress tracker of the user. User id from request: @uid.', [
          '@uid' => $request_uid,
        ]);
        return new JsonResponse($this->utility->getErrorResponse('No user available in session', Response::HTTP_NOT_FOUND));
      }

      // Check if uid is for anonymous or uid in the request
      // matches the one in session.
      if ($request_uid == 0 || $uid !== $request_uid) {
        $this->logger->error('Error while trying to get progress tracker of the user. User id in request doesn`t match the one in session. User id from request: @req_uid. User id in session: @session_uid.', [
          '@req_uid' => $request_uid,
          '@session_uid' => $uid,
        ]);
        return new JsonResponse($this->utility->getErrorResponse('User id in request doesn`t match the one in session.', Response::HTTP_NOT_FOUND));
      }

      $endpoint = sprintf('/customers/apcTierProgressData/customerId/%s', $customer_id);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $data = [];

      if (is_array($response['tier_progress_tracker'])) {
        $values = reset($response['tier_progress_tracker']);
        $data = [
          'nextTierLevel' => $values['tier_code'] ?? '',
          'userPoints' => $values['current_value'] ?? '',
          'nextTierThreshold' => $values['max_value'] ?? '',
        ];
      }

      $responseData = [
        'status' => TRUE,
        'data' => $data,
      ];

      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to get progress tracker of the user. User Id: @uid. Message: @message', [
        '@uid' => $uid,
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
