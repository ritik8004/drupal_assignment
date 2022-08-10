<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;
use App\Service\Config\SystemSettings;

/**
 * Helper class to access XML APIs.
 *
 * @package App\Helper
 */
class XmlAPIHelper {
  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

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
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \App\Helper\APIHelper $api_helper
   *   API Helper.
   */
  public function __construct(LoggerInterface $logger,
                              SystemSettings $settings,
                              APIHelper $api_helper) {
    $this->logger = $logger;
    $this->settings = $settings;
    $this->apiHelper = $api_helper;
  }

  /**
   * Get API response.
   *
   * @param string $wsdl_url
   *   The wsdl url for the soap client.
   * @param string $api_function
   *   The api function name.
   * @param string $api_body
   *   Body of the api.
   *
   * @return object
   *   Response object.
   */
  private function getApiDataWithXml($wsdl_url, $api_function, $api_body) {
    $appointment_settings = $this->settings->getSettings('appointment_settings');

    $xml = '<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns2="http://services.timecommerce.timetrade.com/ws" xmlns:ns3="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:ns4="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <S:Header>
          <ns3:Security>
            <ns3:UsernameToken>
              <ns3:Username>' . $appointment_settings['username'] . '</ns3:Username>
              <ns3:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $appointment_settings['password'] . '</ns3:Password>
            </ns3:UsernameToken>
          </ns3:Security>
        </S:Header>
        <S:Body>'
          . $api_body .
        '</S:Body>
      </S:Envelope>';

    $client = new \SoapClient($wsdl_url, []);
    $soapBody = new \SoapVar($xml, \XSD_ANYXML);

    try {
      $response = $client->__SoapCall($api_function, [$soapBody]);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting @function. Message: @message', [
        '@function' => $api_function,
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }

    return $response;
  }

  /**
   * Get Stores.
   *
   * @return object
   *   Response object.
   */
  public function fetchStores($param) {
    $apiBody = '<ns2:getLocationsByGeoCriteria>
        <locationSearchGeoCriteria>
          <latitude>' . $param['latitude'] . '</latitude>
          <longitude>' . $param['longitude'] . '</longitude>
          <radius>' . $param['radius'] . '</radius>
          <maxNumberOfLocations>' . $param['maxLocations'] . '</maxNumberOfLocations>
          <unit>' . $param['unit'] . '</unit>
        </locationSearchGeoCriteria>
      </ns2:getLocationsByGeoCriteria>';

    $result = $this->getApiDataWithXml($this->apiHelper->getTimetradeBaseUrl() . APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL, 'getLocationsByGeoCriteria', $apiBody);

    return $result;
  }

  /**
   * Get Time slots.
   *
   * @return object
   *   Response object.
   */
  public function fetchTimeSlots($request) {
    $result = [];
    $selected_date = $request->query->get('selectedDate');
    $program = $request->query->get('program');
    $activity = $request->query->get('activity');
    $location = $request->query->get('location');

    $apiBody = '
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
    ';

    if ($selected_date && $program && $activity && $location) {
      $result = $this->getApiDataWithXml($this->apiHelper->getTimetradeBaseUrl() . APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL, 'getAvailableNDateTimeSlotsStartFromDate', $apiBody);
    }
    else {
      $result['error'] = 'Required parameters are missing.';
    }

    return $result;
  }

  /**
   * Update/Insert Client.
   *
   * @return object
   *   Response object.
   */
  public function updateInsertClient($param) {
    $apiBody = '<ns2:updateInsertClient>
      <client>
        <clientExternalId>' . $param['clientExternalId'] . '</clientExternalId>
        <firstName>' . $param['firstName'] . '</firstName>
        <lastName>' . $param['lastName'] . '</lastName>
        <dob>' . $param['dob'] . '</dob>
        <email>' . $param['email'] . '</email>
        <phoneData>
          <mobile>' . $param['mobile'] . '</mobile>
        </phoneData>
        <rulesGroupExternalId>client</rulesGroupExternalId>
        <userGroupExternalId>client</userGroupExternalId>
      </client>
    </ns2:updateInsertClient>';

    $result = $this->getApiDataWithXml($this->apiHelper->getTimetradeBaseUrl() . APIServicesUrls::WSDL_CLIENT_SERVICES_URL, 'updateInsertClient', $apiBody);

    return $result;
  }

  /**
   * Book appointment.
   *
   * @return object
   *   Response object.
   */
  public function bookAppointment($param) {
    $apiBody = '<ns2:bookAppointment>
      <criteria>
        <activityExternalId>' . $param['activity'] . '</activityExternalId>
        <appointmentDurationMin>' . $param['duration'] . '</appointmentDurationMin>
        <locationExternalId>' . $param['location'] . '</locationExternalId>
        <numberOfAttendees>' . $param['attendees'] . '</numberOfAttendees>
        <programExternalId>' . $param['program'] . '</programExternalId>
        <channel>' . $param['channel'] . '</channel>
      </criteria>
      <startDateTime>' . $param['startDateTime'] . '</startDateTime>
      <clientExternalId>' . $param['client'] . '</clientExternalId>
    </ns2:bookAppointment>';

    $result = $this->getApiDataWithXml($this->apiHelper->getTimetradeBaseUrl() . APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL, 'bookAppointment', $apiBody);

    return $result;
  }

}
