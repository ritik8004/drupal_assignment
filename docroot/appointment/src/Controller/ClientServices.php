<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Service\SoapClient;
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
   * SoapClient.
   *
   * @var \App\Service\SoapClient
   */
  protected $client;

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
   * ClientServices constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\SoapClient $client
   *   Soap client service.
   * @param \App\Helper\APIHelper $api_helper
   *   API Helper.
   * @param \App\Helper\XmlAPIHelper $xml_api_helper
   *   Xml API Helper.
   */
  public function __construct(LoggerInterface $logger,
                              SoapClient $client,
                              APIHelper $api_helper,
                              XmlAPIHelper $xml_api_helper) {
    $this->logger = $logger;
    $this->client = $client;
    $this->apiHelper = $api_helper;
    $this->xmlApiHelper = $xml_api_helper;
  }

  /**
   * Update/Insert Client.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Client external Id.
   */
  public function updateInsertClient(Request $request) {
    try {
      $result = $this->xmlApiHelper->updateInsertClient($request);
      $clientExternalId = $result->return->result ?? '';

      return new JsonResponse($clientExternalId);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while inserting/updating client. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  /**
   * Get Questions.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   List of questions.
   */
  public function getQuestions(Request $request) {
    try {
      $client = $this->client->getSoapClient(APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL);
      $locationExternalId = $request->query->get('location');
      $program = $request->query->get('program');
      $activity = $request->query->get('activity');

      if (empty($locationExternalId) || empty($program) || empty($activity)) {
        $message = 'Required details is missing to get questions.';

        $this->logger->error($message);
        throw new \Exception($message);
      }

      $param = [
        'questionCriteria' => [
          'locationExternalId' => $locationExternalId,
          'programExternalId' => $program,
          'activityExternalId' => $activity,
        ],
      ];
      $result = $client->__soapCall('getAppointmentQuestionsByCriteria', [$param]);

      $questions = $result->return->questions ?? [];
      $questionsData = [];

      foreach ($questions as $question) {
        $questionsData[] = [
          'questionExternalId' => $question->questionExternalId ?? '',
          'questionLabel' => $question->questionLabel ?? '',
          'questionType' => $question->questionType ?? '',
          'questionText' => $question->questionText ?? '',
          'required' => $question->required ?? '',
        ];
      }

      return new JsonResponse($questionsData);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching questions. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
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
      $client = $this->client->getSoapClient(APIServicesUrls::WSDL_CLIENT_SERVICES_URL);
      $email = $request->query->get('email');

      if (empty($email)) {
        $message = 'email is required to get client details.';

        $this->logger->error($message);
        throw new \Exception($message);
      }

      $param = [
        'criteria' => [
          'emailAddress' => $email,
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

      throw $e;
    }
  }

}
