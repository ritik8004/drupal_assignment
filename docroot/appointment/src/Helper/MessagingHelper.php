<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;

/**
 * Class MessagingHelper.
 *
 * @package App\Helper
 */
class MessagingHelper {
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
   * MessagingHelper constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Helper\APIHelper $api_helper
   *   API Helper.
   */
  public function __construct(LoggerInterface $logger,
                              APIHelper $api_helper) {
    $this->logger = $logger;
    $this->apiHelper = $api_helper;
    $this->ttBaseUrl = $this->apiHelper->getTimetradeBaseUrl();
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

      if ($result->return->status && $result->return->result != 'SUCCESS') {
        $message = 'sendEmailConfirmation API failed. Booking Id: ' . $appointmentId
          . ' Cause: ' . $result->return->cause
          . ' Status: ' . $result->return->status;

        $this->logger->error($message);
        throw new \Exception($message);
      }

      $apiResult = $result->return->result ?? '';

      $this->logger->notice('Confirmation email is sent for appointment: @appointment_id . API Response: @api_response', [
        '@appointment_id' => $appointmentId,
        '@api_response' => $apiResult,
      ]);

      return $apiResult;
    }
    catch (\Exception $e) {
      $message = 'Error occurred while sending email confirmation.';
      $this->logger->error($message . ' Message: @message . Booking Id: @appointmentId', [
        '@message' => $e->getMessage(),
        '@appointmentId' => $appointmentId,
      ]);
      throw new \Exception($message);
    }
  }

}
