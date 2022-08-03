<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;
use App\Service\Drupal\Drupal;

/**
 * Class Client Helper.
 *
 * @package App\Helper
 */
class ClientHelper {
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
   * Drupal Service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * ClientHelper constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Helper\APIHelper $api_helper
   *   API Helper.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   */
  public function __construct(LoggerInterface $logger,
                              APIHelper $api_helper,
                              Drupal $drupal) {
    $this->logger = $logger;
    $this->apiHelper = $api_helper;
    $this->drupal = $drupal;
    $this->ttBaseUrl = $this->apiHelper->getTimetradeBaseUrl();
  }

  /**
   * Update/Insert Client.
   *
   * @param array $clientData
   *   Array of client data.
   *
   * @return string
   *   clientExternalId.
   */
  public function updateInsertClient(array $clientData) {
    $message = NULL;
    try {
      $param = [
        'client' => [
          'clientExternalId' => $clientData['clientExternalId'] ?? '',
          'firstName' => $clientData['firstName'] ?? '',
          'lastName' => $clientData['lastName'] ?? '',
          'dob' => $clientData['dob'] ?? '',
          'email' => $clientData['email'] ?? '',
          'phoneData' => [
            'mobile' => $clientData['mobile'] ?? '',
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

      if (empty($clientData['firstName']) || empty($clientData['lastName']) || empty($clientData['dob']) || empty($clientData['mobile']) || empty($clientData['email'])) {
        $message = 'Required parameters missing to create a client.';
        $this->logger->error($message . ' Data: @request_data', [
          '@request_data' => json_encode($clientData, JSON_THROW_ON_ERROR),
        ]);
        throw new \Exception($message);
      }

      // Get clientExternalId for the email id if it exists.
      $clientExternalId = $this->apiHelper->checkifBelongstoUser($param['client']['email']);
      if ($clientExternalId) {
        $param['client']['clientExternalId'] = $clientExternalId;
      }

      $userId = $clientData['id'] ?? '';
      if ($userId) {
        // Get user info from backend system.
        $user = $this->apiHelper->getUserInfo();
        if ($user['uid'] == 0 || $user['uid'] !== $userId) {
          $message = sprintf('Userid from request does not match userId of logged in user. Userid from request:%s, Users id:%s', $userId, $user['uid']);

          throw new \Exception($message);
        }
      }

      $client = $this->apiHelper->getSoapClient($this->ttBaseUrl . APIServicesUrls::WSDL_CLIENT_SERVICES_URL);
      $result = $client->__soapCall('updateInsertClient', [$param]);
      $clientExternalId = $result->return->result ?? '';

      // Log on client update/insert.
      $this->logger->info('Client @operation successfully. Data: @params', [
        '@params' => json_encode($clientData, JSON_THROW_ON_ERROR),
        '@operation' => $param['client']['clientExternalId'] ? 'updated' : 'inserted',
      ]);

      return $clientExternalId;
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while operation: @operation client. Message: @message', [
        '@message' => $e->getMessage(),
        '@operation' => $clientData['clientExternalId'] ? 'updated' : 'inserted',
      ]);
      throw new \Exception($message);
    }
  }

  /**
   * Append appointment answers.
   *
   * @return string
   *   Booking Id.
   */
  public function appendAppointmentAnswers($bookingId, $companionData) {
    try {
      $client = $this->apiHelper->getSoapClient($this->ttBaseUrl . APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL);

      if (empty($bookingId)) {
        throw new \Exception('Booking Id is required to save answers.');
      }

      foreach ($companionData as $key => $value) {
        $questionAnswerList[] = [
          'questionExternalId' => $key,
          'answer' => $value,
        ];
      }

      if (empty($questionAnswerList)) {
        throw new \Exception('Empty question answer list in appendAppointmentAnswers.');
      }

      $param = [
        'confirmationNumber' => $bookingId,
        'questionAnswerList' => $questionAnswerList,
      ];
      $result = $client->__soapCall('appendAppointmentAnswers', [$param]);

      if ($result->return->status && $result->return->result != 'SUCCESS') {
        $message = 'appendAppointmentAnswers API failed. Booking ID: ' . $bookingId
          . ' Cause: ' . $result->return->cause
          . ' Status: ' . $result->return->status;

        $this->logger->error($message);
        throw new \Exception($message);
      }

      $apiResult = $result->return->result ?? [];

      $this->logger->notice('Companion details is appended to the appointment booked. API Response: @response', [
        '@response' => $apiResult,
      ]);

      return $apiResult;
    }
    catch (\Exception $e) {
      $message = 'Error occurred while appending booking appointment answers.';
      $this->logger->error($message . 'Message: @message', [
        '@message' => $e->getMessage(),
      ]);
      throw new \Exception($message);
    }
  }

}
