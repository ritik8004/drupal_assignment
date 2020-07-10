<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\XmlAPIHelper;
use App\Service\SoapClient;
use App\Helper\APIServicesUrls;

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
   * SoapClient.
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
   */
  public function __construct(LoggerInterface $logger,
                              XmlAPIHelper $xml_api_helper,
                              SoapClient $client) {
    $this->logger = $logger;
    $this->xmlApiHelper = $xml_api_helper;
    $this->client = $client;
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

}
