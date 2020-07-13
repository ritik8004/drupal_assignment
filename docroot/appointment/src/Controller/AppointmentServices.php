<?php

namespace App\Controller;

use App\Helper\APIServicesUrls;
use App\Service\Drupal\Drupal;
use App\Service\SoapClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\XmlAPIHelper;

/**
 * Class AppointmentServices.
 */
class AppointmentServices {
  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * XmlAPIHelper.
   *
   * @var \App\Helper\XmlAPIHelper
   */
  protected $xmlApiHelper;

  /**
   * Drupal Service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * Soap client.
   *
   * @var \App\Service\SoapClient
   */
  protected $client;

  /**
   * AppointmentServices constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Helper\XmlAPIHelper $xml_api_helper
   *   Xml API Helper.
   * @param \App\Service\SoapClient $client
   *   Soap client service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   */
  public function __construct(LoggerInterface $logger,
                              XmlAPIHelper $xml_api_helper,
                              SoapClient $client,
                              Drupal $drupal) {
    $this->logger = $logger;
    $this->xmlApiHelper = $xml_api_helper;
    $this->client = $client;
    $this->drupal = $drupal;
  }

  /**
   * Get Available time slots.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Time slot data from API.
   */
  public function getTimeSlots(Request $request) {
    try {
      $result = $this->xmlApiHelper->fetchTimeSlots($request);

      return new JsonResponse($result);

    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting time slots from TT API. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  /**
   * Book Appointment.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Booking Id.
   */
  public function bookAppointment(Request $request) {
    try {
      $result = $this->xmlApiHelper->bookAppointment($request);
      $bookingId = $result->return->result ?? '';

      return new JsonResponse($bookingId);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while booking appointment. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  /**
   * Append appointment answers.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Booking Id.
   */
  public function appendAppointmentAnswers(Request $request) {
    try {
      $client = $this->client->getSoapClient(APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL);
      $request_content = json_decode($request->getContent(), TRUE);

      $bookingId = $request_content['bookingId'] ?? '';
      if (empty($bookingId)) {
        throw new \Exception('Booking Id is required to save answers.');
      }

      foreach ($request_content as $key => $value) {
        if ($key === 'bookingId') {
          continue;
        }
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

      if ($result->return->status && $result->return->result === 'FAILED') {
        $message = $result->return->cause;

        $this->logger->error($message);
        throw new \Exception($message);
      }

      $apiResult = $result->return->result ?? [];

      return new JsonResponse($apiResult);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while appending booking appointment answers. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  /**
   * Get Client details.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Client details.
   */
  public function getAppointments(Request $request) {
    try {
      $client = $this->client->getSoapClient(APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL);
      $clientExternalId = $request->query->get('client');
      $userId = $request->query->get('id');

      if (empty($clientExternalId)) {
        $message = 'clientExternalId is required to get appointment details.';

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

      $startDate = date("Y-m-d\TH:i:s.000\Z");
      $endDate = date("Y-12-31\T23:59:59.999\Z");

      $param = [
        'criteria' => [
          'clientExternalId' => $clientExternalId,
          'includeCancelledAppointments' => FALSE,
          'suppressSubAppointmentDetail' => FALSE,
        ],
        'startDateTime' => $startDate,
        'endDateTime' => $endDate,

      ];
      $result = $client->__soapCall('getAppointmentsByCriteriaAppointmentDateRange', [$param]);

      if (!is_array($result->return->appointments)) {
        $temp = $result->return->appointments;
        unset($result->return->appointments);
        $result->return->appointments[0] = $temp;
      }

      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching appointments. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  /**
   * Get companions by appointment confirmation number.
   */
  public function getCompanionByAppointmentId(Request $request) {
    try {
      $client = $this->client->getSoapClient(APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL);
      $appointmentId = $request->query->get('appointment');
      $userId = $request->query->get('id');

      if (empty($appointmentId)) {
        $message = 'Appointment Id is required to get companion details.';

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
        'confirmationNumber' => $appointmentId,
      ];
      $result = $client->__soapCall('getAppointmentAnswersByAppointmentConfirmationNumber', [$param]);
      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching companion details. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }

  }

}
