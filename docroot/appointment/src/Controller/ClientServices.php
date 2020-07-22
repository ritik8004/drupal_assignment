<?php

namespace App\Controller;

use App\Service\Drupal\Drupal;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\APIHelper;
use App\Helper\APIServicesUrls;
use App\Helper\XmlAPIHelper;

/**
 * Class ClientServices.
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
    try {
      $request_content = json_decode($request->getContent(), TRUE);

      $param = [
        'clientExternalId' => $request_content['clientExternalId'] ?? '',
        'firstName' => $request_content['firstName'] ?? '',
        'lastName' => $request_content['lastName'] ?? '',
        'dob' => $request_content['dob'] ?? '',
        'mobile' => $request_content['mobile'] ?? '',
        'email' => $request_content['email'] ?? '',
      ];

      if (empty($param['firstName']) || empty($param['lastName']) || empty($param['dob']) || empty($param['mobile']) || empty($param['email'])) {
        $message = 'Required parameters missing to create a client.';
        $this->logger->error($message . ' Data: @request_data', [
          '@request_data' => json_encode($param),
        ]);
        throw new \Exception($message);
      }

      // Get clientExternalId for the email id if it exists.
      $clientExternalId = $this->apiHelper->checkifBelongstoUser($param['email']);
      if ($clientExternalId) {
        $param['clientExternalId'] = $clientExternalId;
      }

      $result = $this->xmlApiHelper->updateInsertClient($param);
      $clientExternalId = $result->return->result ?? '';

      return new JsonResponse($clientExternalId);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while inserting/updating client. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      return new JsonResponse($this->apiHelper->getErrorMessage(), 400);
    }
  }

  /**
   * Get Client details by email.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Client details.
   */
  public function getClientsByCriteria(Request $request) {
    try {
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $userId = $request->query->get('id');

      if (empty($userId)) {
        $message = 'User Id is required to get client details.';

        $this->logger->error($message);
        throw new \Exception($message);
      }

      // Authenticate logged in user by matching userid from request and Drupal.
      $user = $this->drupal->getSessionUserInfo();
      if ($user['uid'] !== $userId) {
        $message = 'Requested not authenticated.';

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

      return new JsonResponse($clientData);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching client details. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      return new JsonResponse($this->apiHelper->getErrorMessage(), 400);
    }
  }

}
