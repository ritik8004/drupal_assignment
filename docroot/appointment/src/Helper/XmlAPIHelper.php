<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;
use App\Service\Config\SystemSettings;

/**
 * Class XmlAPIHelper.
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
   * ConfigurationServices constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   */
  public function __construct(LoggerInterface $logger,
                              SystemSettings $settings) {
    $this->logger = $logger;
    $this->settings = $settings;
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
  public function fetchStores($request) {
    $requestQuery = $request->query;

    $apiBody = '<ns2:getLocationsByGeoCriteria>
        <locationSearchGeoCriteria>
          <latitude>' . $requestQuery->get('latitude') . '</latitude>
          <longitude>' . $requestQuery->get('longitude') . '</longitude>
          <radius>' . $requestQuery->get('radius') . '</radius>
          <maxNumberOfLocations>' . $requestQuery->get('max-locations') . '</maxNumberOfLocations>
          <unit>' . $requestQuery->get('unit') . '</unit>
        </locationSearchGeoCriteria>
      </ns2:getLocationsByGeoCriteria>';

    $result = $this->getApiDataWithXml(APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL, 'getLocationsByGeoCriteria', $apiBody);

    return $result;
  }

  /**
   * Update/Insert Client.
   *
   * @return object
   *   Response object.
   */
  public function updateInsertClient($request) {
    $request_content = json_decode($request->getContent(), TRUE);

    $apiBody = '<ns2:updateInsertClient>
      <client>
        <clientExternalId>' . $request_content['clientExternalId'] . '</clientExternalId>
        <firstName>' . $request_content['firstName'] . '</firstName>
        <lastName>' . $request_content['lastName'] . '</lastName>
        <dob>' . $request_content['dob'] . '</dob>
        <phoneData>
          <mobile>' . $request_content['mobile'] . '</mobile>
        </phoneData>
        <rulesGroupExternalId>client</rulesGroupExternalId>
        <userGroupExternalId>client</userGroupExternalId>
      </client>
    </ns2:updateInsertClient>';

    $result = $this->getApiDataWithXml(APIServicesUrls::WSDL_CLIENT_SERVICES_URL, 'updateInsertClient', $apiBody);

    return $result;
  }

  /**
   * Book appointment.
   *
   * @return object
   *   Response object.
   */
  public function bookAppointment($request) {
    $requestQuery = $request->query;

    $apiBody = '<ns2:bookAppointment>
      <criteria>
        <activityExternalId>' . $requestQuery->get('activity') . '</activityExternalId>
        <appointmentDurationMin>' . $requestQuery->get('duration') . '</appointmentDurationMin>
        <locationExternalId>' . $requestQuery->get('location') . '</locationExternalId>
        <numberOfAttendees>' . $requestQuery->get('attendees') . '</numberOfAttendees>
        <programExternalId>' . $requestQuery->get('program') . '</programExternalId>
        <channel>webpage</channel>
      </criteria>
      <startDateTime>' . $requestQuery->get('start-date-time') . '</startDateTime> 
      <clientExternalId>' . $requestQuery->get('client') . '</clientExternalId>
    </ns2:bookAppointment>';

    $result = $this->getApiDataWithXml(APIServicesUrls::WSDL_APPOINTMENT_SERVICES_URL, 'bookAppointment', $apiBody);

    return $result;
  }

}
