<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;
use App\Service\Drupal\Drupal;

/**
 * Class Helper.
 *
 * @package App\Helper
 */
class Helper {
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
   * Helper constructor.
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
   * Group Store timings like: Sunday - Wednesday (10 AM - 10 PM) etc..
   *
   * @param array $weeklySchedules
   *   Array of store schedule.
   *
   * @return array
   *   Array of grouped store timing.
   */
  public function groupStoreTimings(array $weeklySchedules) {
    $weeklySchedulesData = $firstDay = $lastDay = [];

    foreach ($weeklySchedules as $weeklySchedule) {
      $weekDay = $weeklySchedule->weekDay ?? '';
      $startTime = $weeklySchedule->localStartTime ?? '';
      $endTime = $weeklySchedule->localEndTime ?? '';
      // 24-hour time to 12-hour time
      $timeSlot = date("g:i a", strtotime($startTime)) . ' - ' . date("g:i a", strtotime($endTime));
      $schedule = [
        'day' => $weekDay,
        'time' => $timeSlot,
      ];

      // Group Store timings.
      if (empty($firstDay)) {
        $firstDay = $schedule;
      }
      elseif (empty($lastDay)) {
        $lastDay = $schedule;
      }
      elseif ($timeSlot === $lastDay['time']) {
        $lastDay = $schedule;
      }
      else {
        // Current timeslot is different so store the first and
        // last day schedule in main array and create a new first and last day.
        $weeklySchedulesData[] = [
          'day' => $firstDay['day'] . ' - ' . $lastDay['day'],
          'timeSlot' => $firstDay['time'],
        ];
        $firstDay = $schedule;
        $lastDay = [];
      }
    }

    // Store the last value of firstDay and lastDay in the
    // main array of store schedule.
    if (!empty($firstDay)) {
      if (!empty($lastDay)) {
        $weeklySchedulesData[] = [
          'day' => $firstDay['day'] . ' - ' . $lastDay['day'],
          'timeSlot' => $firstDay['time'],
        ];
      }
      else {
        $weeklySchedulesData[] = [
          'day' => $firstDay['day'],
          'timeSlot' => $firstDay['time'],
        ];
      }
    }

    return $weeklySchedulesData;
  }

  /**
   * Calculate distance between 2 coordinates.
   *
   * @param string $lat1
   *   Latitude 1.
   * @param string $lon1
   *   Longitude 1.
   * @param string $lat2
   *   Latitude 2.
   * @param string $lon2
   *   Longitude 2.
   * @param string $unit
   *   Unit of the distance.
   *
   * @return string
   *   Distance (Default distance unit is miles).
   */
  public function distance($lat1, $lon1, $lat2, $lon2, $unit) {
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
      return 0;
    }
    else {
      $latFrom = deg2rad($lat1);
      $lonFrom = deg2rad($lon1);
      $latTo = deg2rad($lat2);
      $lonTo = deg2rad($lon2);

      $latDelta = $latTo - $latFrom;
      $lonDelta = $lonTo - $lonFrom;

      $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
      $distance = $angle * 3959;

      if ($unit === "kilometers") {
        $distance *= 1.609344;
      }

      return number_format((float) $distance, 2, '.', '');
    }
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
          '@request_data' => json_encode($clientData),
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
        // Authenticate user by matching userid from request and Drupal.
        $user = $this->drupal->getSessionUserInfo();
        if ($user['uid'] !== $userId) {
          $message = 'Userid: ' . $userId . ' from endpoint doesn\'t match userId: ' . $user['uid'] . ' of logged in user.';

          throw new \Exception($message);
        }
      }

      $client = $this->apiHelper->getSoapClient($this->ttBaseUrl . APIServicesUrls::WSDL_CLIENT_SERVICES_URL);
      $result = $client->__soapCall('updateInsertClient', [$param]);
      $clientExternalId = $result->return->result ?? '';

      return $clientExternalId;
    }
    catch (\Exception $e) {
      $message = 'Error occurred while inserting/updating client.';
      $this->logger->error($message . ' Message: @message', [
        '@message' => $e->getMessage(),
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

      if ($result->return->status && $result->return->result === 'FAILED') {
        $message = $result->return->cause;

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

  /**
   * Send Email Confirmation.
   */
  public function sendEmailConfirmation($appointmentId) {
    try {
      if (empty($appointmentId)) {
        $message = 'Appointment Id is required.';
        throw new \Exception($message);
      }

      $client = $this->apiHelper->getSoapClient($this->ttBaseUrl . APIServicesUrls::WSDL_MESSAGING_SERVICES_URL);
      $param = [
        'confirmationNumber' => $appointmentId,
      ];
      $result = $client->__soapCall('sendEmailConfirmation', [$param]);

      if ($result->return->status && $result->return->result === 'FAILED') {
        $message = $result->return->cause;

        $this->logger->error($message);
        throw new \Exception($message);
      }

      $apiResult = $result->return->result ?? [];

      $this->logger->notice('Confirmation email is sent for appointment : @appointment_id', [
        '@appointment_id' => $apiResult,
      ]);

      return $apiResult;
    }
    catch (\Exception $e) {
      $message = 'Error occurred while sending email confirmation.';
      $this->logger->error($message . ' Message: @message', [
        '@message' => $e->getMessage(),
      ]);
      throw new \Exception($message);
    }
  }

}
