<?php

namespace App\Controller\LoyaltyClub;

use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Aura\CustomerHelper;
use App\Service\Cart;

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
   * Service for aura customer.
   *
   * @var \App\Service\Aura\CustomerHelper
   */
  protected $auraCustomerHelper;

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
   * @param \App\Service\Aura\CustomerHelper $aura_customer_helper
   *   Aura customer helper service.
   * @param \App\Service\Cart $cart
   *   Cart service.
   */
  public function __construct(
    MagentoApiWrapper $magento_api_wrapper,
    LoggerInterface $logger,
    Drupal $drupal,
    Utility $utility,
    CustomerHelper $aura_customer_helper,
    Cart $cart
  ) {
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->logger = $logger;
    $this->drupal = $drupal;
    $this->utility = $utility;
    $this->auraCustomerHelper = $aura_customer_helper;
    $this->cart = $cart;
  }

  /**
   * Update User's AURA Status.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return success/failure response.
   */
  public function apcStatusUpdate(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);
    $data = $this->auraCustomerHelper->prepareAuraUserStatusUpdateData($request_content);

    if (!empty($data['error'])) {
      $this->logger->error('Error while trying to update user AURA Status. Data: @data.', [
        '@data' => json_encode($data),
      ]);
      return new JsonResponse($data);
    }

    try {
      // Get user details from session.
      $customer_id = $this->cart->getDrupalInfo('customer_id');
      $uid = $this->cart->getDrupalInfo('uid');

      // Check if we have user in session.
      if (empty($customer_id) || empty($uid)) {
        $this->logger->error('Error while trying to update user AURA Status. No user available in session. User id from request: @uid.', [
          '@uid' => $request_content['uid'],
        ]);
        return new JsonResponse($this->utility->getErrorResponse('No user available in session', Response::HTTP_NOT_FOUND));
      }

      // Check if uid in the request matches the one in session.
      if ($uid !== $request_content['uid']) {
        $this->logger->error("Error while trying to update user AURA Status. User id in request doesn't match the one in session. User id from request: @req_uid. User id in session: @session_uid.", [
          '@req_uid' => $request_content['uid'],
          '@session_uid' => $uid,
        ]);
        return new JsonResponse($this->utility->getErrorResponse("User id in request doesn't match the one in session.", Response::HTTP_NOT_FOUND));
      }

      $data['statusUpdate']['customerId'] = $customer_id;
      $url = 'customers/apc-status-update';
      $response = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);

      // On API success, update the user AURA Status in Drupal.
      if ($response) {
        $auraData = [
          'uid' => $request_content['uid'],
          'apcLinkStatus' => $request_content['apcLinkStatus'],
        ];
        $updated = $this->drupal->updateUserAuraInfo($auraData);

        // Check if user aura status was updated successfully in drupal.
        if (!$updated) {
          $message = 'Error while trying to update user AURA Status field in Drupal.';
          $this->logger->error($message . ' User Id: @uid, Customer Id: @customer_id, Aura Status: @aura_status.', [
            '@uid' => $request_content['uid'],
            '@aura_status' => $request_content['apcLinkStatus'],
            '@customer_id' => $customer_id,
          ]);
          return new JsonResponse($this->utility->getErrorResponse($message, 500));
        }
      }

      $responseData = [
        'status' => $response,
      ];

      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to update AURA Status for user with customer id @customer_id. Request Data: @request_data. Message: @message', [
        '@customer_id' => $customer_id,
        '@request_data' => json_encode($data),
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
