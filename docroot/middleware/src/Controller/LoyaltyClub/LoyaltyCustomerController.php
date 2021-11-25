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
use App\Service\Aura\ValidationHelper;
use App\Service\Aura\AuraErrorCodes;

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
    $customer_id = $this->cart->getDrupalInfo('customer_id');
    $uid = $this->cart->getDrupalInfo('uid');
    $response_data = [];

    if (empty($customer_id) || empty($uid)) {
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
      return new JsonResponse($this->utility->getErrorResponse(AuraErrorCodes::MOBILE_ALREADY_REGISTERED_MSG, AuraErrorCodes::MOBILE_ALREADY_REGISTERED_CODE));
    }

    // Call search API to check if given email
    // is already registered or not.
    $search_response = $this->auraSearchHelper->search('email', $request_content['email']);

    if (!empty($search_response['data']['apc_identifier_number'])) {
      $this->logger->error('Error while trying to do loyalty club sign up. Email address @email is already registered.', [
        '@email' => $request_content['email'],
      ]);
      return new JsonResponse($this->utility->getErrorResponse(AuraErrorCodes::EMAIL_ALREADY_REGISTERED_MSG, AuraErrorCodes::EMAIL_ALREADY_REGISTERED_CODE));
    }

    try {
      // Get user details from session.
      $customer_id = $this->cart->getDrupalInfo('customer_id');
      $data['customer'] = array_merge($request_content, ['isVerified' => 'Y']);

      if (!empty($customer_id)) {
        $data['customer']['customerId'] = $customer_id;
      }

      $url = 'customers/quick-enrollment';
      $response = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);
      $responseData = [
        'status' => TRUE,
        'data' => $response,
      ];

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
   * Set/Unset loyalty card in cart.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Returns api response.
   */
  public function updateLoyaltyCard(Request $request) {
    try {
      $request_content = json_decode($request->getContent(), TRUE);
      $responseData = [];

      // Check if action is not empty.
      if (empty($request_content['action'])) {
        $this->logger->error('Error while trying to set loyalty card in cart. Action key `add/remove` is missing. Request Data: @data', [
          '@data' => json_encode($request_content),
        ]);
        return new JsonResponse($this->utility->getErrorResponse('Action key `add/remove` is missing.', Response::HTTP_NOT_FOUND));
      }

      // Check if required data is present in request for `add` action.
      if ($request_content['action'] === 'add' && (empty($request_content['type']) || empty($request_content['value']))) {
        $this->logger->error('Error while trying to set loyalty card in cart. Required parameters missing. Request Data: @data', [
          '@data' => json_encode($request_content),
        ]);

        if ($request_content['type'] === 'email') {
          $error = AuraErrorCodes::EMPTY_EMAIL;
        }
        elseif ($request_content['type'] === 'apcNumber') {
          $error = AuraErrorCodes::EMPTY_CARD;
        }
        elseif ($request_content['type'] === 'phone') {
          $error = AuraErrorCodes::EMPTY_MOBILE;
        }

        return new JsonResponse($this->utility->getErrorResponse($error ?? '', 'MISSING_DATA'));
      }

      // Get cart id from session.
      $cart_id = $this->cart->getCartId();

      if (empty($cart_id)) {
        $this->logger->error('Error while trying to set loyalty card in cart. Cart id not available.');
        return new JsonResponse($this->utility->getErrorResponse('Cart id not available.', Response::HTTP_NOT_FOUND));
      }

      // Request Data.
      $data = [
        'quote_id' => $cart_id,
        'identifier_no' => '',
      ];

      if ($request_content['action'] === 'add') {
        $search_response = $this->auraSearchHelper->searchUserDetails($request_content);

        if (!empty($search_response['error'])) {
          $this->logger->error('Error while trying to set loyalty card in cart. No card found. Request Data: @data.', [
            '@data' => json_encode($request_content),
          ]);
          return new JsonResponse($search_response);
        }

        $data['identifier_no'] = $search_response['data']['apc_identifier_number'];
      }

      $response = $this->auraCustomerHelper->setLoyaltyCard($data);
      $responseData = [
        'status' => $response,
        'data' => $search_response['data'] ?? [],
      ];

      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to set loyalty card in cart. Request Data: @data. Message: @message', [
        '@data' => json_encode($request_content),
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

  /**
   * Reward Activity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return users reward activities.
   */
  public function getRewardActivity(Request $request) {
    try {
      $request_content = $request->query->all();

      // Get user details from session.
      $customer_id = $this->cart->getDrupalInfo('customer_id');
      $uid = $this->cart->getDrupalInfo('uid');

      // Check if we have user in session.
      if (empty($customer_id) || empty($uid)) {
        $this->logger->error('Error while trying to get reward activity of the user. User id from request: @uid.', [
          '@uid' => $request_content['uid'],
        ]);
        return new JsonResponse($this->utility->getErrorResponse('No user available in session', Response::HTTP_NOT_FOUND));
      }

      // Check if uid is for anonymous or uid in the request
      // matches the one in session.
      if ($request_content['uid'] == 0 || $uid !== $request_content['uid']) {
        $this->logger->error('Error while trying to get reward activity of the user. User id in request doesn`t match the one in session. User id from request: @req_uid. User id in session: @session_uid.', [
          '@req_uid' => $request_content['uid'],
          '@session_uid' => $uid,
        ]);
        return new JsonResponse($this->utility->getErrorResponse('User id in request doesn`t match the one in session.', Response::HTTP_NOT_FOUND));
      }

      // API call to get reward activity.
      $data = $this->auraCustomerHelper->getRewardActivity(
        $customer_id,
        $request_content['fromDate'] ?? '',
        $request_content['toDate'] ?? '',
        $request_content['maxResults'] ?? 0,
        $request_content['channel'] ?? '',
        $request_content['partnerCode'] ?? ''
      );

      // Check if request is to get last transaction and response is not empty.
      if (empty($request_content['fromDate'])
        && empty($request_content['toDate'])
        && (int) $request_content['maxResults'] === 1
        && !empty($data)) {
        // If last transaction is before given duration, return empty.
        $lastTransactionData = reset($data);
        if (strtotime($lastTransactionData['date']) < strtotime('-' . $request_content['duration'] . 'months')) {
          return new JsonResponse([
            'status' => TRUE,
            'data' => [],
          ]);
        }
        // API call to get reward activity data.
        $dateObject = new \DateTime($lastTransactionData['date']);
        $fromDate = $dateObject->format('Y-m-01');
        $toDate = $dateObject->format('Y-m-t');

        $data = $this->auraCustomerHelper->getRewardActivity(
          $customer_id,
          $fromDate,
          $toDate,
          0,
          $request_content['channel'] ?? '',
          $request_content['partnerCode'] ?? ''
        );
      }

      // Return if we already have error from API response.
      if (!empty($data['error'])) {
        return new JsonResponse($data);
      }

      $responseData = [
        'status' => TRUE,
        'data' => $data,
      ];
      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to get reward activity of the user. Request Data: @data. Message: @message', [
        '@data' => json_encode($request_content),
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
