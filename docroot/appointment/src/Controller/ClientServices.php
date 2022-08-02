<?php

namespace App\Controller;

use App\Cache\AppointmentJsonResponse;
use App\Service\Drupal\Drupal;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\APIHelper;
use App\Helper\APIServicesUrls;
use App\Helper\XmlAPIHelper;

/**
 * Class Client Services.
 */
class ClientServices {
  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * APIHelper.
   *
   * @var \App\Helper\APIHelper
   */
  protected $apiHelper;

  /**
   * XmlAPIHelper.
   *
   * @var \App\Helper\XmlAPIHelper
   */
  protected $xmlApiHelper;

  /**
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * ClientServices constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Helper\APIHelper $api_helper
   *   API Helper.
   * @param \App\Helper\XmlAPIHelper $xml_api_helper
   *   Xml API Helper.
   * @param \App\Service\Drupal $drupal
   *   Drupal service.
   */
  public function __construct(LoggerInterface $logger,
                              APIHelper $api_helper,
                              XmlAPIHelper $xml_api_helper,
                              Drupal $drupal) {
    $this->logger = $logger;
    $this->apiHelper = $api_helper;
    $this->xmlApiHelper = $xml_api_helper;
    $this->drupal = $drupal;
    $this->serviceUrl = $this->apiHelper->getTimetradeBaseUrl() . APIServicesUrls::WSDL_CLIENT_SERVICES_URL;
  }

  /**
   * Update/Insert Client.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Client external Id.
   */
  public function updateInsertClient(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);

    try {
      $param = [
        'client' => [
          'clientExternalId' => $request_content['clientExternalId'] ?? '',
          'firstName' => $request_content['firstName'] ?? '',
          'lastName' => $request_content['lastName'] ?? '',
          'dob' => $request_content['dob'] ?? '',
          'email' => $request_content['email'] ?? '',
          'phoneData' => [
            'mobile' => $request_content['mobile'] ?? '',
          ],
          'rulesGroupExternalId' => 'client',
          'userGroupExternalId' => 'client',
          'applyTimeZone' => TRUE,
          'customerId' => 0,
          'passwordExpired' => FALSE,
          'requirePasswordChange' => FALSE,
          'disableEmail' => FALSE,
        ],
      ];

      if (empty($request_content['firstName']) || empty($request_content['lastName']) || empty($request_content['dob']) || empty($request_content['mobile']) || empty($request_content['email'])) {
        $message = 'Required parameters missing to create a client.';
        $this->logger->error($message . ' Data: @request_data', [
          '@request_data' => json_encode($request_content, JSON_THROW_ON_ERROR),
        ]);
        throw new \Exception($message);
      }

      // Get clientExternalId for the email id if it exists.
      $clientExternalId = $this->apiHelper->checkifBelongstoUser($param['client']['email']);
      if ($clientExternalId) {
        $param['client']['clientExternalId'] = $clientExternalId;
      }

      $userId = $request_content['id'] ?? '';
      if ($userId) {
        // Authenticate user by matching userid from request and Drupal.
        $user = $this->drupal->getSessionUserInfo();
        if ($user['uid'] !== $userId) {
          $message = sprintf('Userid from request does not match userId of logged in user. Userid from request:%s, Users id:%s', $userId, $user['uid']);

          throw new \Exception($message);
        }
      }

      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $result = $client->__soapCall('updateInsertClient', [$param]);
      $clientExternalId = $result->return->result ?? '';

      // Log on client update/insert.
      $this->logger->info('Client @operation successfully. Data: @params', [
        '@params' => json_encode($request_content, JSON_THROW_ON_ERROR),
        '@operation' => $request_content['clientExternalId'] ? 'updated' : 'inserted',
      ]);

      return new AppointmentJsonResponse($clientExternalId);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while @operation client. Message: @message, Data: @params', [
        '@message' => $e->getMessage(),
        '@params' => json_encode($request_content, JSON_THROW_ON_ERROR),
        '@operation' => $request_content['clientExternalId'] ? 'updated' : 'inserted',
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
    }
  }

  /**
   * Get Client details by email.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Client details.
   */
  public function getClientsByCriteria(Request $request) {
    $userId = $request->query->get('id');

    try {
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);

      if (empty($userId)) {
        $message = 'User Id is required to get client details.';

        $this->logger->error($message);
        throw new \Exception($message);
      }

      // Get user info from backend system.
      $user = $this->apiHelper->getUserInfo();
      if ($userId == 0 || $user['uid'] !== $userId) {
        $message = sprintf('Userid from request does not match userId of logged in user. Userid from request:%s, Users id:%s', $userId, $user['uid']);

        $this->logger->error($message);
        throw new \Exception($message);
      }

      $param = [
        'criteria' => [
          'emailAddress' => $user['email'],
          'exactMatchOnly' => TRUE,
          'hideDisabled' => TRUE,
        ],
      ];
      $result = $client->__soapCall('getClientsByCriteria', [$param]);

      $clientData = [];
      $clientArray = $result->return->clients ?? [];

      if (!empty($clientArray)) {
        $clientData = [
          'clientExternalId' => $clientArray->clientExternalId ?? '',
          'firstName' => $clientArray->firstName ?? '',
          'lastName' => $clientArray->lastName ?? '',
          'dob' => $clientArray->dob ? date('Y-m-d', strtotime($clientArray->dob)) : '',
          'email' => $clientArray->email ?? '',
          'mobile' => $clientArray->phoneData->mobile ?? '',
        ];
      }

      return new AppointmentJsonResponse($clientData);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching client details. Message: @message, User: @user', [
        '@message' => $e->getMessage(),
        '@user' => $userId,
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
    }
  }

}
