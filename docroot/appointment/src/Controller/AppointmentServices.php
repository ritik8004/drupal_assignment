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
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
    }
  }

  /**
   * Book Appointment.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Booking Id.
   */
  public function bookAppointment(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);

    $appointmentId = $request_content['appointment'] ?? '';
    $userId = $request_content['id'] ?? '';
    try {
      // Book New appointment.
      if (!$appointmentId) {
        $param = [
          'activity' => $request_content['activity'] ?? '',
          'duration' => $request_content['duration'] ?? '',
          'location' => $request_content['location'] ?? '',
          'attendees' => $request_content['attendees'] ?? '',
          'program' => $request_content['program'] ?? '',
          'channel' => $request_content['channel'] ?? '',
          'startDateTime' => $request_content['start_date_time'] ?? '',
          'client' => $request_content['client'] ?? '',
        ];

        if (empty($param['activity']) || empty($param['duration']) || empty($param['location']) || empty($param['attendees']) || empty($param['program']) || empty($param['channel']) || empty($param['startDateTime']) || empty($param['client'])) {
          $message = 'Required parameters missing to book appointment.';
          $this->logger->error($message . ' Data: @request_data', [
            '@request_data' => json_encode($param),
          ]);
          throw new \Exception($message);
        }

        if ($userId) {
          // Authenticate user by matching userid from request and Drupal.
          $user = $this->drupal->getSessionUserInfo();
          if ($user['uid'] !== $userId) {
            $message = 'Userid from endpoint doesn\'t match userId of logged in user.';

            throw new \Exception($message);
          }
          // Match Client in request and client id of user.
          $clientExternalId = $this->apiHelper->checkifBelongstoUser($user['email']);
          if ($param['client'] != $clientExternalId) {
            $message = 'Client Id ' . $param['client'] . ' does not belong to logged in user.';

            throw new \Exception($message);
          }

        }

        $result = $this->xmlApiHelper->bookAppointment($param);

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
          'locationExternalId' => $request_content['location'],
          'appointmentDurationMin' => $request_content['duration'],
          'numberOfAttendees' => $request_content['attendees'],
          'setupDurationMin' => 0,
        ],
        'confirmationNumber' => $request_content['appointment'],
        'resourceAvailabilityRequired' => FALSE,
      ];
      $newTime = strtotime($request_content['start_date_time']);
      $originalTime = strtotime($request_content['originaltime']);
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
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
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
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
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

      foreach ($result->return->appointments as $key => $appointment) {
        $clientExternalId = $this->apiHelper->checkifBelongstoUser($user['email']);
        if ($appointment->clientExternalId != $clientExternalId) {
          unset($result->return->appointments[$key]);
        }
      }

      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching appointments. Message: @message', [
        '@message' => $e->getMessage(),
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
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

      $companions = [];
      $k = 0;
      if (!empty($result->return)) {

        // Put FirstName, LastName in for each companion in one array.
        foreach ($result->return as $item) {
          if (strstr($item->question, 'First')) {
            if (property_exists($item, 'answer')) {
              $companions[$k]['firstName'] = $item->answer;
              $companions[$k]['lastName'] = '';
              $companions[$k]['dob'] = '';
              $companions[$k]['customer'] = $k + 1;
            }
          }
          if (strstr($item->question, 'Last')) {
            if (property_exists($item, 'answer')) {
              $companions[$k]['firstName'] = $companions[$k]['firstName'];
              $companions[$k]['lastName'] = $item->answer;
              $companions[$k]['dob'] = '';
              $companions[$k]['customer'] = $k + 1;
            }
          }
          if (strstr($item->question, 'Date')) {
            if (property_exists($item, 'answer')) {
              $companions[$k]['firstName'] = $companions[$k]['firstName'];
              $companions[$k]['lastName'] = $companions[$k]['lastName'];
              $companions[$k]['dob'] = $item->answer;
              $companions[$k]['customer'] = $k + 1;
            }
            $k++;
          }
        }

        // Remove companion array if firstName doesn't exist.
        foreach ($companions as $key => &$value) {
          if (empty($value['firstName'])) {
            unset($companions[$key]);
          }
        }
      }

      return new JsonResponse($companions);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching companion details. Message: @message', [
        '@message' => $e->getMessage(),
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
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

      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $param = [
        'confirmationNumber' => $appointmentId,
      ];
      $appointmentData = $client->__soapCall('getAppointmentByConfirmationNumber', [$param]);

      // Check if Appointment Belongs to user only.
      if (property_exists($appointmentData->return, 'appointment')) {
        $clientExternalId = $appointmentData->return->appointment->clientExternalId;
        if ($this->apiHelper->checkifBelongstoUser($user['email']) != $clientExternalId) {
          $message = 'Appointment ' . $appointmentId . ' does not belong logged in user.';

          throw new \Exception($message);
        }
      }

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
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
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
      $locationExternalId = $request->query->get('location') ?? '';
      $program = $request->query->get('program') ?? '';
      $activity = $request->query->get('activity') ?? '';

      $param = [
        'questionCriteria' => [
          'locationExternalId' => $locationExternalId,
          'programExternalId' => $program,
          'activityExternalId' => $activity,
        ],
      ];

      if (empty($locationExternalId) || empty($program) || empty($activity)) {
        $message = 'Required details is missing to get questions.';
        $this->logger->error($message . ' Data: @request_data', [
          '@request_data' => json_encode($param),
        ]);
        throw new \Exception($message);
      }

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
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
    }
  }

  /**
   * Get Appointment details for logged in user.
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
      $appointmentData = $client->__soapCall('getAppointmentByConfirmationNumber', [$param]);

      // Check if Appointment Belongs to user onyl.
      if (property_exists($appointmentData->return, 'appointment')) {
        $clientExternalId = $appointmentData->return->appointment->clientExternalId;
        if ($this->apiHelper->checkifBelongstoUser($user['email']) == $clientExternalId) {
          return new JsonResponse($appointmentData);
        }
      }

      throw new \Exception('Appointment not found for id: ' . $appointment);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching appointment with id @appid for user @user. Message: @message', [
        '@message' => $e->getMessage(),
        '@appid' => $appointment,
        '@user' => $user,
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
    }
  }

}
