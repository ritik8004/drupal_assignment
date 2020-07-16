<?php

namespace App\Controller;

use App\Helper\APIServicesUrls;
use App\Service\Drupal\Drupal;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\XmlAPIHelper;
use App\Helper\APIHelper;

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
   * APIHelper.
   *
   * @var \App\Helper\APIHelper
   */
  protected $apiHelper;

  /**
   * AppointmentServices constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Helper\XmlAPIHelper $xml_api_helper
   *   Xml API Helper.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Helper\APIHelper $api_helper
   *   API Helper.
   */
  public function __construct(LoggerInterface $logger,
                              XmlAPIHelper $xml_api_helper,
                              Drupal $drupal,
                              APIHelper $api_helper) {
    $this->logger = $logger;
    $this->xmlApiHelper = $xml_api_helper;
    $this->drupal = $drupal;
    $this->apiHelper = $api_helper;
    $this->serviceUrl = $this->apiHelper->getTimetradeBaseUrl() . APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL;
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
    $appointmentId = $request->query->get('appointment');
    $userId = $request->query->get('id');
    try {
      // Book New appointment.
      if (!$appointmentId) {
        $result = $this->xmlApiHelper->bookAppointment($request);
        $bookingId = $result->return->result ?? '';
        return new JsonResponse($bookingId);
      }

      // Rebook appointment.
      if (empty($appointmentId) || empty($userId)) {
        $message = 'Appointment Id and user Id are required to get appointment details.';

        throw new \Exception($message);
      }

      // Authenticate logged in user by matching userid from request and Drupal.
      $user = $this->drupal->getSessionUserInfo();
      if ($user['uid'] !== $userId) {
        $message = 'Userid from endpoint doesn\'t match userId of logged in user.';

        throw new \Exception($message);
      }

      $param = [
        'criteria' => [
          'locationExternalId' => $request->query->get('location'),
          'appointmentDurationMin' => $request->query->get('duration'),
          'numberOfAttendees' => $request->query->get('attendees'),
          'setupDurationMin' => 0,
        ],
        'confirmationNumber' => $request->query->get('appointment'),
        'resourceAvailabilityRequired' => FALSE,
      ];
      $newTime = strtotime($request->query->get('start-date-time'));
      $originalTime = strtotime($request->query->get('originaltime'));
      if ($newTime != $originalTime) {
        $param['startDateTime'] = $newTime;
      }

      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $result = $client->__soapCall('reBookAppointment', [$param]);
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
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
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
   * Get Appointment details.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Appointment details.
   */
  public function getAppointments(Request $request) {
    try {
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $clientExternalId = $request->query->get('client');
      $userId = $request->query->get('id');

      if (empty($clientExternalId)) {
        $message = 'clientExternalId is required to get appointment details.';

        throw new \Exception($message);
      }

      // Authenticate logged in user by matching userid from request and Drupal.
      $user = $this->drupal->getSessionUserInfo();
      if ($user['uid'] !== $userId) {
        $message = 'Requested not authenticated.';

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
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $appointmentId = $request->query->get('appointment');
      $userId = $request->query->get('id');

      if (empty($appointmentId)) {
        $message = 'Appointment Id is required to get companion details.';

        throw new \Exception($message);
      }

      // Authenticate logged in user by matching userid from request and Drupal.
      $user = $this->drupal->getSessionUserInfo();
      if ($user['uid'] !== $userId) {
        $message = 'Request not authenticated.';

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

  /**
   * Cancel an appointment.
   */
  public function cancelAppointment(Request $request) {
    try {
      $appointmentId = $request->query->get('appointment');
      $userId = $request->query->get('id');
      if ($appointmentId == '' || $userId == '') {
        $message = 'Appointment Id and user Id are required parameters.';
        throw new \Exception($message);
      }

      // Authenticate logged in user by matching userid from request and Drupal.
      $user = $this->drupal->getSessionUserInfo();
      if ($user['uid'] !== $userId) {
        $message = 'Request not authenticated.';

        throw new \Exception($message);
      }

      $client = $this->client->getSoapClient($this->serviceUrl);
      $param = [
        'confirmationNumber' => $appointmentId,
      ];
      $result = $client->__soapCall('cancelAppointment', [$param]);
      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while deleting an appointment. Message: @message', [
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
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
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
   * Get Appointment details.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Appointment details.
   */
  public function getAppointmentDetails(Request $request) {
    $appointment = $request->query->get('appointment');
    $userId = $request->query->get('id');

    try {
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);

      if (empty($appointment) || empty($userId)) {
        $message = 'Appointment Id and user Id are required to get appointment details.';

        throw new \Exception($message);
      }

      // Authenticate logged in user by matching userid from request and Drupal.
      $user = $this->drupal->getSessionUserInfo();
      if ($user['uid'] !== $userId) {
        $message = 'Userid from endpoint doesn\'t match userId of logged in user.';

        throw new \Exception($message);
      }

      $param = [
        'confirmationNumber' => $appointment,
      ];
      $result = $client->__soapCall('getAppointmentByConfirmationNumber', [$param]);

      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching appointment with id @appid for user @user. Message: @message', [
        '@message' => $e->getMessage(),
        '@appid' => $appointment,
        '@user' => $user,
      ]);

      throw $e;
    }
  }

}
