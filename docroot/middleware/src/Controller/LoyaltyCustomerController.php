<?php

namespace App\Controller;

use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Aura\CustomerHelper;
use App\Service\Drupal\Drupal;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Magento\MagentoApiWrapper;

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
   * Magento API Wrapper service.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApiWrapper;

  /**
   * LoyaltyCustomerController constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\Aura\CustomerHelper $aura_customer_helper
   *   Aura customer helper service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API wrapper service.
   */
  public function __construct(
    LoggerInterface $logger,
    Utility $utility,
    CustomerHelper $aura_customer_helper,
    Drupal $drupal,
    MagentoApiWrapper $magento_api_wrapper
  ) {
    $this->logger = $logger;
    $this->utility = $utility;
    $this->auraCustomerHelper = $aura_customer_helper;
    $this->drupal = $drupal;
    $this->magentoApiWrapper = $magento_api_wrapper;
  }

  /**
   * Returns the loyalty customer details.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The loyalty customer details for the current user or error message.
   */
  public function getCustomerDetails(Request $request) {
    $fetchStatus = $request->get('fetchStatus') ?? TRUE;
    $fetchPoints = $request->get('fetchPoints') ?? TRUE;
    $fetchTier = $request->get('fetchTier') ?? TRUE;
    $updateDrupal = $request->get('update') ?? TRUE;
    $tier = $request->get('tier') ?? '';
    $status = $request->get('status') ?? '';

    $sessionCustomerInfo = $this->drupal->getSessionCustomerInfo();
    $customer_id = $sessionCustomerInfo['customer_id'];
    $response_data = [];

    if (empty($customer_id)) {
      $this->logger->error('Error while trying to fetch loyalty points for customer. No customer available in session.');
      return new JsonResponse($this->utility->getErrorResponse('No user in session', Response::HTTP_NOT_FOUND));
    }

    // Call helper to get customer information only if fetch status
    // is not false in request.
    if ($fetchStatus) {
      $customer_info = $this->auraCustomerHelper->getCustomerInfo($customer_id);

      if (empty($customer_info['error'])) {
        $response_data = array_merge($response_data, $customer_info);
      }
    }

    // Call helper to get customer point details only if fetch points
    // is not false in request.
    if ($fetchPoints) {
      $customer_points = $this->auraCustomerHelper->getCustomerPoints($customer_id);

      if (empty($customer_points['error'])) {
        $response_data = array_merge($response_data, $customer_points);
      }
    }

    // Call helper to get customer tier details only if fetch tier
    // is not false in request.
    if ($fetchTier) {
      $customer_tier = $this->auraCustomerHelper->getCustomerTier($customer_id);

      if (empty($customer_tier['error'])) {
        $response_data = array_merge($response_data, $customer_tier);
      }
    }

    // Compare aura status and tier from drupal and API response and if
    // they are different then call Drupal API to update the values.
    if ($updateDrupal && !empty($response_data)) {
      $updatedData = [];

      if ((int) $status !== $response_data['auraStatus']) {
        $updatedData['apcLinkStatus'] = $response_data['auraStatus'];
      }

      if ($tier !== $response_data['tier']) {
        $updatedData['tier'] = $response_data['tier'];
      }

      if (!empty($updatedData)) {
        $updatedData['uid'] = $sessionCustomerInfo['uid'];
        $this->drupal->updateUserAuraInfo($updatedData);
      }
    }

    return new JsonResponse($response_data);
  }

  /**
   * Loyalty Club Signup.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return API response status.
   */
  public function loyaltyClubSignUp(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);

    if (empty($request_content['firstname']) || empty($request_content['lastname']) || empty($request_content['email']) || empty($request_content['mobile'])) {
      $this->logger->error('Error while trying to do Quick Enrollment. First name, last name, email and mobile number is required. Data: @data', [
        '@data' => $request_content,
      ]);
      return new JsonResponse($this->utility->getErrorResponse('First name, last name, email and mobile number is required.', Response::HTTP_NOT_FOUND));
    }

    try {
      // Get user details from session.
      $user = $this->drupal->getSessionCustomerInfo();
      $data['customer'] = array_merge($request_content, ['isVerified' => 'Y']);

      $url = 'customers/quick-enrollment';
      $response = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);
      $responseData = [
        'status' => TRUE,
        'data' => $response,
      ];

      // On API success, update user AURA Status in Drupal for logged in user.
      if (!empty($user['uid']) && is_array($response) && !empty($response['apc_link'])) {
        $auraData = [
          'uid' => $user['uid'],
          'apcLinkStatus' => $response['apc_link'],
        ];
        $updated = $this->drupal->updateUserAuraInfo($auraData);;

        // Check if user aura status was updated successfully in drupal.
        if (!$updated) {
          $message = 'Error while trying to update user AURA Status field in Drupal after quick enrollment.';
          $this->logger->error($message . ' User Id: @uid, Customer Id: @customer_id, Aura Status: @aura_status.', [
            '@uid' => $user['uid'],
            '@customer_id' => $user['customer_id'],
            '@aura_status' => $response['apc_link'],
          ]);
          return new JsonResponse($this->utility->getErrorResponse($message, 500));
        }
      }

      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to do Quick Enrollment. Request Data: @data, Message: @message', [
        '@data' => $request_content,
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
