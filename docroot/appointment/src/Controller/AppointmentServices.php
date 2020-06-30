<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Service\SoapClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
   * ConfigurationServices constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\SoapClient $client
   *   Soap client service.
   * @param \App\Helper\APIHelper $api_helper
   *   API Helper.
   */
  public function __construct(LoggerInterface $logger,
                              SoapClient $client,
                              APIHelper $api_helper) {
    $this->logger = $logger;
    $this->client = $client;
    $this->apiHelper = $api_helper;
  }

  /**
   * Get Available time slots.
   *
   * @return json
   *   Time slot data from API.
   */
  public function getTimeSlots(Request $request) {
    try {
      $selected_date = $request->query->get('selected_date');
      $program = $request->query->get('program');
      $activity = $request->query->get('activity');
      $location = $request->query->get('location');
      $wsdl = "https://api-stage.timetradesystems.co.uk/soap/AppointmentServices?wsdl";
      $client_new = new \SoapClient($wsdl, []);
      $xml = '
      <S:Envelope  xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"  xmlns:ns2="http://services.timecommerce.timetrade.com/ws"  xmlns:ns3="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <S:Header>
          <ns3:Security>
              <ns3:UsernameToken>
                  <ns3:Username>bootsapiuser</ns3:Username>
                  <ns3:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">jG4@dF0p</ns3:Password>
              </ns3:UsernameToken>
          </ns3:Security>
        </S:Header>
        <S:Body>
          <ns2:getAvailableNDateTimeSlotsStartFromDate>
              <criteria>
                  <activityExternalId>' . $activity . '</activityExternalId>
                  <locationExternalId>' . $location . '</locationExternalId>
                  <programExternalId>' . $program . '</programExternalId>
              </criteria>
              <startDateTime>' . $selected_date . 'T00:00:00.000+03:00</startDateTime>
              <endDateTime>' . $selected_date . 'T23:59:59.999+03:00</endDateTime>
              <numberOfSlots>500</numberOfSlots>
          </ns2:getAvailableNDateTimeSlotsStartFromDate>
        </S:Body>
      </S:Envelope>
      ';

      $args = [new \SoapVar($xml, XSD_ANYXML)];
      $response = $client_new->__soapCall('getAvailableNDateTimeSlotsStartFromDate', $args);
      $timeSlotsData = [];

      foreach ($response->return as $value) {
        $datetime = new \DateTime($value->appointmentSlotTime, new \DateTimeZone('Europe/London'));
        $hours = $datetime->format('H');
        if ($hours < "12") {
          $timeSlotsData['morning'][] = [
            'time' => $value->appointmentSlotTime,
            'duration' => $value->lengthinMin,
            'resource' => $value->resourceExternalIds,
          ];
        }
        elseif ($hours >= "12" && $hours < "17") {
          $timeSlotsData['afternoon'][] = [
            'time' => $value->appointmentSlotTime,
            'duration' => $value->lengthinMin,
            'resource' => $value->resourceExternalIds,
          ];
        }
        else {
          $timeSlotsData['evening'][] = [
            'time' => $value->appointmentSlotTime,
            'duration' => $value->lengthinMin,
            'resource' => $value->resourceExternalIds,
          ];
        }

      }
      return new JsonResponse($timeSlotsData);

    }
    catch (\SoapFault $e) {
      $this->logger->error('Error occurred while getting activities. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

}
