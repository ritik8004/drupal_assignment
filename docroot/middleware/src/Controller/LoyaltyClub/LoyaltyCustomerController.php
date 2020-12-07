<?php

namespace App\Controller\LoyaltyClub;

use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Aura\CustomerHelper;
use App\Service\Drupal\Drupal;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\Aura\SearchHelper;
use App\Service\Cart;
use App\Service\Magento\CartActions;
use App\Service\Aura\ValidationHelper;

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
   * Service for aura search helper.
   *
   * @var \App\Service\Aura\SearchHelper
   */
  protected $auraSearchHelper;

  /**
   * Service for cart interaction.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * Validation Helper service.
   *
   * @var \App\Service\Aura\ValidationHelper
   */
  protected $validationHelper;

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
   * @param \App\Service\Aura\SearchHelper $aura_search_helper
   *   Aura search helper service.
   * @param \App\Service\Cart $cart
   *   Cart service.
   * @param \App\Service\Aura\ValidationHelper $validation_helper
   *   Validation Helper service.
   */
  public function __construct(
    LoggerInterface $logger,
    Utility $utility,
    CustomerHelper $aura_customer_helper,
    Drupal $drupal,
    MagentoApiWrapper $magento_api_wrapper,
    SearchHelper $aura_search_helper,
    Cart $cart,
    ValidationHelper $validation_helper
  ) {
    $this->logger = $logger;
    $this->utility = $utility;
    $this->auraCustomerHelper = $aura_customer_helper;
    $this->drupal = $drupal;
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->auraSearchHelper = $aura_search_helper;
    $this->cart = $cart;
    $this->validationHelper = $validation_helper;
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

      if ($response_data['auraStatus'] && (int) $status !== $response_data['auraStatus']) {
        $updatedData['apcLinkStatus'] = $response_data['auraStatus'];
      }

      if ($response_data['tier'] && $tier !== $response_data['tier']) {
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

    if (empty($request_content['firstname']) || empty($request_content['lastname'])) {
      $this->logger->error('Error while trying to do loyalty club sign up. First name and last name is required. Data: @data', [
        '@data' => json_encode($request_content),
      ]);
      return new JsonResponse($this->utility->getErrorResponse('INVALID_NAME_ERROR', 500));
    }

    $validation = $this->validationHelper->validateInput('email', $request_content['email']);

    if (!empty($validation['error'])) {
      return new JsonResponse($validation);
    }

    $validation = $this->validationHelper->validateInput('mobile', $request_content['mobile']);

    if (!empty($validation['error'])) {
      return new JsonResponse($validation);
    }

    // Call search API to check if given mobile number
    // is already registered or not.
    $search_response = $this->auraSearchHelper->search('phone', $request_content['mobile']);

    if (!empty($search_response['data']['apc_identifier_number'])) {
      $this->logger->error('Error while trying to do loyalty club sign up. Mobile number @mobile is already registered.', [
        '@mobile' => $request_content['mobile'],
      ]);
      return new JsonResponse($this->utility->getErrorResponse('form_error_mobile_already_registered', 'mobile_already_registered'));
    }

    // Call search API to check if given email
    // is already registered or not.
    $search_response = $this->auraSearchHelper->search('email', $request_content['email']);

    if (!empty($search_response['data']['apc_identifier_number'])) {
      $this->logger->error('Error while trying to do loyalty club sign up. Email address @email is already registered.', [
        '@email' => $request_content['email'],
      ]);
      return new JsonResponse($this->utility->getErrorResponse('form_error_email_already_registered', 'email_already_registered'));
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
        $updated = $this->drupal->updateUserAuraInfo($auraData);

        // Check if user aura status was updated successfully in drupal.
        if (!$updated) {
          $message = 'Error while trying to update user AURA Status field in Drupal after loyalty club sign up.';
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
      $this->logger->notice('Error while trying to do loyalty club sign up. Request Data: @data, Message: @message', [
        '@data' => json_encode($request_content),
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

  /**
   * Search APC user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return users loyalty details.
   */
  public function searchApcUser(Request $request) {
    try {
      $request_content = json_decode($request->getContent(), TRUE);
      $type = $request_content['type'];
      $value = $request_content['value'];

      // Check if required data is present in request.
      if (empty($type) || empty($value)) {
        $this->logger->error('Error while trying to search APC user. Required parameters missing. Request Data: @data', [
          '@data' => json_encode($request_content),
        ]);
        return new JsonResponse($this->utility->getErrorResponse('Required parameters missing.', Response::HTTP_NOT_FOUND));
      }

      $endpoint = sprintf('/customers/apc-search/%s/%s', $type, $value);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);
      $responseData = [
        'status' => TRUE,
        'data' => $response,
      ];
      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to search APC user. Request Data: @data. Message: @message', [
        '@data' => json_encode($request_content),
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

  /**
   * Cart Loyalty Update.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return users loyalty details.
   */
  public function cartLoyaltyUpdate(Request $request) {
    try {
      $request_content = json_decode($request->getContent(), TRUE);
      $type = $request_content['type'];
      $value = $request_content['value'];
      $responseData = [];

      // Check if required data is present in request.
      if (empty($type) || empty($value)) {
        $this->logger->error('Error while trying to process cart to attach loyalty card. Required parameters missing. Request Data: @data', [
          '@data' => json_encode($request_content),
        ]);
        return new JsonResponse($this->utility->getErrorResponse('Required parameters missing.', Response::HTTP_NOT_FOUND));
      }

      $search_response = $this->auraSearchHelper->search($type, $value);

      // When card found for the given user details,
      // then attach the card to cart.
      if (!empty($search_response['data']['apc_identifier_number'])) {
        $data['extension'] = (object) [
          'action' => CartActions::CART_REFRESH,
          'loyalty_card' => $search_response['data']['apc_identifier_number'],
        ];

        $cart_data = $this->cart->updateCart($data);

        if (!empty($cart_data['cart']['extension_attributes']['loyalty_card'])) {
          $search_response['status'] = TRUE;
          $responseData = $search_response;
        }
      }

      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to process cart to attach loyalty card. Request Data: @data. Message: @message', [
        '@data' => json_encode($request_content),
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
