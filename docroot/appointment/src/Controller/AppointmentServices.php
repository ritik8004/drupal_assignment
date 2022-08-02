<?php

namespace App\Controller;

use App\Cache\AppointmentJsonResponse;
use App\Helper\APIServicesUrls;
use App\Cache\Cache;
use App\Service\Drupal\Drupal;
use App\Translation\TranslationHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\XmlAPIHelper;
use App\Helper\APIHelper;
use App\Helper\ClientHelper;
use App\Helper\MessagingHelper;

/**
 * Class Appointment Services.
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
   * Cache Helper.
   *
   * @var \App\Cache\Cache
   */
  protected $cache;

  /**
   * Translation Helper.
   *
   * @var \App\Translation\TranslationHelper
   */
  protected $translationHelper;

  /**
   * Helper.
   *
   * @var \App\Helper\ClientHelper
   */
  protected $clientHelper;

  /**
   * Helper.
   *
   * @var \App\Helper\MessagingHelper
   */
  protected $messagingHelper;

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
   * @param \App\Cache\Cache $cache
   *   Cache Helper.
   * @param \App\Translation\TranslationHelper $translationHelper
   *   Translation Helper.
   * @param \App\Helper\ClientHelper $clientHelper
   *   Client Helper.
   * @param \App\Helper\MessagingHelper $messagingHelper
   *   Client Helper.
   */
  public function __construct(LoggerInterface $logger,
                              XmlAPIHelper $xml_api_helper,
                              Drupal $drupal,
                              APIHelper $api_helper,
                              Cache $cache,
                              TranslationHelper $translationHelper,
                              ClientHelper $clientHelper,
                              MessagingHelper $messagingHelper) {
    $this->logger = $logger;
    $this->xmlApiHelper = $xml_api_helper;
    $this->drupal = $drupal;
    $this->apiHelper = $api_helper;
    $this->serviceUrl = $this->apiHelper->getTimetradeBaseUrl() . APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL;
    $this->cache = $cache;
    $this->translationHelper = $translationHelper;
    $this->clientHelper = $clientHelper;
    $this->messagingHelper = $messagingHelper;
  }

  /**
   * Get Available time slots.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Time slot data from API.
   */
  public function getTimeSlots(Request $request) {
    $selected_date = $request->query->get('selectedDate');
    $program = $request->query->get('program');
    $activity = $request->query->get('activity');
    $location = $request->query->get('location');
    $duration = $request->query->get('duration') ?? 30;
    $params = [
      'criteria' => [
        'activityExternalId' => $activity,
        'locationExternalId' => $location,
        'programExternalId' => $program,
        'appointmentDurationMin' => $duration,
        'numberOfAttendees' => 1,
        'setupDurationMin' => 0,
      ],
      'startDateTime' => $selected_date . 'T00:00:00.000+03:00',
      'endDateTime' => $selected_date . 'T23:59:59.999+03:00',
      'numberOfSlots' => $this->apiHelper->getNumberOfSlots(),
    ];
    try {

      if (empty($selected_date) || empty($program) || empty($activity) || empty($location)) {
        $message = 'Required parameters missing to get time slots.';
        $this->logger->error($message . ' Data: @request_data', [
          '@request_data' => json_encode($params, JSON_THROW_ON_ERROR),
        ]);
        throw new \Exception($message);
      }

      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $result = $client->__soapCall('getAvailableNDateTimeSlotsStartFromDate', [$params]);

      return new AppointmentJsonResponse($result);

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
    $userId = $request_content['user'] ?? '';
    try {
      // Update/insert client.
      $clientExternalId = $this->clientHelper->updateInsertClient($request_content['clientData']);

      // Book New appointment.
      if (!$appointmentId) {
        $param = [
          'criteria' => [
            'activityExternalId' => $request_content['activity'] ?? '',
            'appointmentDurationMin' => $request_content['duration'] ?? '',
            'locationExternalId' => $request_content['location'] ?? '',
            'numberOfAttendees' => $request_content['attendees'] ?? '',
            'programExternalId' => $request_content['program'] ?? '',
            'channel' => $request_content['channel'] ?? '',
            'setupDurationMin' => 0,
          ],
          'startDateTime' => $request_content['start_date_time'] ?? '',
          'clientExternalId' => $clientExternalId ?? '',
        ];

        if (empty($request_content['activity']) || empty($request_content['duration']) || empty($request_content['location']) || empty($request_content['attendees']) || empty($request_content['program']) || empty($request_content['channel']) || empty($request_content['start_date_time']) || empty($param['clientExternalId'])) {
          $message = 'Required parameters missing to book appointment.';
          $this->logger->error($message . ' Data: @request_data', [
            '@request_data' => json_encode($request_content, JSON_THROW_ON_ERROR),
          ]);
          throw new \Exception($message);
        }

        // If userId is 0, then anonymous user booking appointment.
        if ($userId) {
          // Get user info from backend system.
          $user = $this->apiHelper->getUserInfo();
          if ($user['uid'] !== $userId) {
            $message = sprintf('Userid from request does not match userId of logged in user. Userid from request:%s, Users id:%s', $userId, $user['uid']);

            throw new \Exception($message);
          }
          // Match Client in request and client id of user.
          $clientExternalId = $this->apiHelper->checkifBelongstoUser($user['email']);
          if ($param['clientExternalId'] != $clientExternalId) {
            $message = 'Client Id ' . $param['clientExternalId'] . ' does not belong to logged in user.';

            throw new \Exception($message);
          }
        }

        // Get clientExternalId for invalidating cache.
        $tags = [
          'appointments_by_clientId_' . $clientExternalId,
        ];
        $this->cache->tagInvalidation($tags);

        $client = $this->apiHelper->getSoapClient($this->serviceUrl);
        $result = $client->__soapCall('bookAppointment', [$param]);

        $bookingId = $result->return->result ?? '';

        // Trigger email confirmation if we have the bookingId.
        if (!empty($bookingId)) {
          $this->messagingHelper->sendEmailConfirmation($bookingId);

          // Append companion data if we have the bookingId and companionData.
          $companionData = $request_content['companionData'] ?? '';
          if (!empty($companionData)) {
            $this->clientHelper->appendAppointmentAnswers($bookingId, $companionData);
          }
        }

        // Log appointment booked.
        $this->logger->info('Appointment booked successfully. Appointment:@appointment, User:@userid, Data:@params,', [
          '@params' => json_encode($request_content, JSON_THROW_ON_ERROR),
          '@appointment' => $bookingId,
          '@userid' => $userId,
        ]);

        return new AppointmentJsonResponse($bookingId);
      }

      // Rebook appointment.
      if (empty($appointmentId) || empty($userId)) {
        $message = 'Appointment Id and user Id are required to get appointment details.';

        throw new \Exception($message);
      }

      // Get user info from backend system.
      $user = $this->apiHelper->getUserInfo();
      if ($userId == 0 || $user['uid'] !== $userId) {
        $message = sprintf('Userid from request does not match userId of logged in user. Userid from request:%s, Users id:%s', $userId, $user['uid']);

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

      // Append companion data if we have the bookingId and companionData.
      $companionData = $request_content['companionData'] ?? '';
      if (!empty($companionData) && !empty($bookingId)) {
        $this->clientHelper->appendAppointmentAnswers($bookingId, $companionData);
      }

      $tags = [
        'appointments_by_clientId_' . $clientExternalId,
        'appointment_' . $bookingId,
      ];
      $this->cache->tagInvalidation($tags);

      // Log appointment re-booked.
      $this->logger->info('Appointment re-booked successfully. Appointment:@appointment, User:@userid, Data:@params,', [
        '@params' => json_encode($request_content, JSON_THROW_ON_ERROR),
        '@appointment' => $request_content['appointment'],
        '@userid' => $userId,
      ]);

      return new AppointmentJsonResponse($bookingId);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while booking appointment. Message: @message , Data: @params', [
        '@message' => $e->getMessage(),
        '@params' => json_encode($request_content, JSON_THROW_ON_ERROR),
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
    $langcode = $request->query->get('langcode');
    $clientExternalId = $request->query->get('client');
    $userId = $request->query->get('id');

    try {
      if (empty($clientExternalId)) {
        $message = 'clientExternalId is required to get appointment details.';

        throw new \Exception($message);
      }

      // Get user info from backend system.
      $user = $this->apiHelper->getUserInfo();
      if ($userId == 0 || $user['uid'] !== $userId) {
        $message = sprintf('Userid from request does not match userId of logged in user. Userid from request:%s, Users id:%s', $userId, $user['uid']);

        throw new \Exception($message);
      }

      $cacheKey = 'appointments_by_clientId_' . $clientExternalId;
      $item = $this->cache->getItemByTagAware($cacheKey, $langcode);
      if ($item) {
        $result = $item;
      }

      // Set start date as 6 month before current date.
      $startDate = date('Y-m-d\TH:i:s.000\Z', strtotime(date('Y-m-d') . '-6 months'));
      // Set end date as 6 months after current date.
      $endDate = date('Y-m-d\T23:59:59.999\Z', strtotime(date('Y-m-d') . '+6 months'));

      $param = [
        'criteria' => [
          'clientExternalId' => $clientExternalId,
          'includeCancelledAppointments' => FALSE,
          'suppressSubAppointmentDetail' => FALSE,
        ],
        'startDateTime' => $startDate,
        'endDateTime' => $endDate,

      ];

      if (empty($result)) {
        $client = $this->apiHelper->getSoapClient($this->serviceUrl);
        $result = $client->__soapCall('getAppointmentsByCriteriaAppointmentDateRange', [$param]);
      }

      if (!is_array($result->return->appointments)) {
        $temp = $result->return->appointments;
        unset($result->return->appointments);
        $result->return->appointments[0] = $temp;
      }

      foreach ($result->return->appointments as $key => &$appointment) {
        $clientExternalId = $this->apiHelper->checkifBelongstoUser($user['email']);
        if ($appointment->clientExternalId != $clientExternalId) {
          unset($result->return->appointments[$key]);
        }

        // Add Translation.
        $appointment->programName = $this->translationHelper->getTranslation(
          $appointment->programName,
          $langcode
        );
        $appointment->activityName = $this->translationHelper->getTranslation(
          $appointment->activityName,
          $langcode
        );

      }

      $this->cache->setItemWithTags($cacheKey, $result, $cacheKey, $langcode);

      return new AppointmentJsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching appointments. Message: @message, Client: @client, User: @user', [
        '@message' => $e->getMessage(),
        '@client' => $clientExternalId,
        '@user' => $userId,
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
    }
  }

  /**
   * Get companions by appointment confirmation number.
   */
  public function getCompanionByAppointmentId(Request $request) {
    $appointmentId = $request->query->get('appointment');
    $userId = $request->query->get('id');

    try {
      if (empty($appointmentId)) {
        $message = 'Appointment Id is required to get companion details.';

        throw new \Exception($message);
      }

      // Get user info from backend system.
      $user = $this->apiHelper->getUserInfo();
      if ($userId == 0 || $user['uid'] !== $userId) {
        $message = sprintf('Userid from request does not match userId of logged in user. Userid from request:%s, Users id:%s', $userId, $user['uid']);

        throw new \Exception($message);
      }

      // Get companions from cache.
      $cacheKey = 'companion_' . $appointmentId;
      $langcode = $request->query->get('langcode');
      $item = $this->cache->getItemByTagAware($cacheKey, $langcode);
      if ($item) {
        return new AppointmentJsonResponse($item);
      }

      $param = [
        'confirmationNumber' => $appointmentId,
      ];
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $result = $client->__soapCall('getAppointmentAnswersByAppointmentConfirmationNumber', [$param]);

      $companions = [];
      if (!empty($result->return)) {
        // Divide array into chunk of 3 as we are getting
        // question / answer data in group series of 3.
        foreach (array_chunk($result->return, 3) as $key => $value) {
          foreach ($value as $item) {
            // Put FirstName, LastName in for each companion in one array.
            if (strstr($item->question, 'First')) {
              if (property_exists($item, 'answer')) {
                $companions[$key]['firstName'] = $item->answer;
              }
            }
            elseif (strstr($item->question, 'Last')) {
              if (property_exists($item, 'answer')) {
                $companions[$key]['lastName'] = $item->answer;
              }
            }
            elseif (strstr($item->question, 'Date')) {
              if (property_exists($item, 'answer')) {
                $companions[$key]['dob'] = $item->answer;
              }
            }
          }
        }

        // Remove companion array if firstName doesn't exist.
        foreach ($companions as $key => &$value) {
          if (empty($value['firstName'])) {
            unset($companions[$key]);
          }
        }
      }

      // Set companion cache using tag same as appointment for invalidation.
      $tag = 'appointment_' . $appointmentId;
      $this->cache->setItemWithTags($cacheKey, $companions, $tag, $langcode);

      return new AppointmentJsonResponse($companions);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching companion details. Message: @message, Appointment: @appointment, User: @user', [
        '@message' => $e->getMessage(),
        '@appointment' => $appointmentId,
        '@user' => $userId,
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
    }
  }

  /**
   * Cancel an appointment.
   */
  public function cancelAppointment(Request $request) {
    $clientExternalId = NULL;
    $request_content = json_decode($request->getContent(), TRUE);
    $appointmentId = $request_content['appointment'];
    $userId = $request_content['id'];

    try {
      if ($appointmentId == '' || $userId == '') {
        $message = 'Appointment Id and user Id are required parameters.';
        throw new \Exception($message);
      }

      // Get user info from backend system.
      $user = $this->apiHelper->getUserInfo();
      if ($userId == 0 || $user['uid'] !== $userId) {
        $message = sprintf('Userid from request does not match userId of logged in user. Userid from request:%s, Users id:%s', $userId, $user['uid']);

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

      // Invalidate Users appointment cache.
      $tags = [
        'appointments_by_clientId_' . $clientExternalId,
        'appointment_' . $appointmentId,
      ];
      $this->cache->tagInvalidation($tags);

      // Log successfully appointment canceled.
      $this->logger->info('Appointment cancelled successfully. Appointment:@appointment, UserId:@userid', [
        '@appointment' => $appointmentId,
        '@userid' => $userId,
      ]);

      return new AppointmentJsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while deleting an appointment. Message: @message, Appointment: @appointment, User: @user', [
        '@message' => $e->getMessage(),
        '@appointment' => $appointmentId,
        '@user' => $userId,
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

      $cacheKey = 'questions';
      $item = $this->cache->getItem($cacheKey);
      if ($item) {
        return new AppointmentJsonResponse($item, TRUE);
      }
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
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

      $this->cache->setItem($cacheKey, $questionsData);
      return new AppointmentJsonResponse($questionsData, TRUE);
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

      // Get user info from backend system.
      $user = $this->apiHelper->getUserInfo();
      if ($userId == 0 || $user['uid'] !== $userId) {
        $message = sprintf('Userid from request does not match userId of logged in user. Userid from request:%s, Users id:%s', $userId, $user['uid']);

        throw new \Exception($message);
      }

      $param = [
        'confirmationNumber' => $appointment,
      ];

      $cacheKey = 'appointment_' . $appointment;
      $langcode = $request->query->get('langcode');
      $item = $this->cache->getItemByTagAware($cacheKey, $langcode);
      if ($item) {
        // Check if Appointment Belongs to user only.
        if (property_exists($item->return, 'appointment')) {
          $clientExternalId = $item->return->appointment->clientExternalId;
          if ($this->apiHelper->checkifBelongstoUser($user['email']) == $clientExternalId) {
            return new AppointmentJsonResponse($item);
          }
        }
      }

      $appointmentData = $client->__soapCall('getAppointmentByConfirmationNumber', [$param]);

      // Check if Appointment Belongs to user onyl.
      if (property_exists($appointmentData->return, 'appointment')) {
        $clientExternalId = $appointmentData->return->appointment->clientExternalId;
        if ($this->apiHelper->checkifBelongstoUser($user['email']) == $clientExternalId) {

          // Add Translations.
          $appointmentData->return->appointment->programName = $this->translationHelper->getTranslation(
            $appointmentData->return->appointment->programName,
            $langcode
          );
          $appointmentData->return->appointment->activityName = $this->translationHelper->getTranslation(
            $appointmentData->return->appointment->activityName,
            $langcode
          );

          $this->cache->setItemWithTags($cacheKey, $appointmentData, $cacheKey, $langcode);
          return new AppointmentJsonResponse($appointmentData);
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
