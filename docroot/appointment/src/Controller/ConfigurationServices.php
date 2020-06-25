<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Service\SoapClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\APIHelper;
use App\Helper\APIServicesUrls;

/**
 * Class ConfigurationServices.
 */
class ConfigurationServices {
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
   * Get Programs.
   *
   * @return json
   *   Program data from API.
   */
  public function getPrograms() {
    try {
      $client = $this->client->getSoapClient(APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL);

      $param = ['locationExternalId' => $this->apiHelper->getlocationExternalId(APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL)];
      $result = $client->__soapCall('getPrograms', [$param]);
      $programs = $result->return->programs;
      $programData = [];

      foreach ($programs ?? [] as $program) {
        if ($program->isEnabled) {
          $programData[] = [
            'id' => $program->programExternalId,
            'name' => $program->programName,
          ];
        }
      }

      return new JsonResponse($programData);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting programs. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  /**
   * Get Programs.
   *
   * @return json
   *   Program data from API.
   */
  public function getActivities(Request $request) {
    try {
      $client = $this->client->getSoapClient(APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL);

      $program = $request->query->get('program');
      $param = [
        'locationExternalId' => $this->apiHelper->getlocationExternalId(APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL),
        'programExternalId' => $program,
      ];
      $result = $client->__soapCall('getActivities', [$param]);
      $activities = $result->return->activities;
      $activityData = [];

      foreach ($activities ?? [] as $activity) {
        if ($activity->isEnabled) {
          $activityData[] = [
            'id' => $activity->activityExternalId,
            'name' => $activity->activityName,
            'description' => $activity->description,
          ];
        }
      }

      return new JsonResponse($activityData);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting activities. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  /**
   * Get Stores By Geo Criteria .
   *
   * @return json
   *   Stores data from API.
   */
  public function getStores(Request $request) {
    // @TODO: Need to discuss this API call.
    // It works with xml but not with SoapVar.
    try {
      // $client = $this->client->getSoapClient(APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL);
      // $requestQuery = $request->query;

      // $param = [
      //   'locationSearchCriteria' => [
      //     'locationExternalId' => $this->apiHelper->getlocationExternalId(APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL),
      //     'locationGroupId' => 'Boots',
      //     'exactMatchOnly' => FALSE,
      //   ],
      //   'locationSearchGeoCriteria' => [
      //     'latitude' => $requestQuery->get('latitude'),
      //     'longitude' => $requestQuery->get('longitude'),
      //     'radius' => $requestQuery->get('radius'),
      //     'maxNumberOfLocations' => $requestQuery->get('max-locations'),
      //     'unit' => $requestQuery->get('unit')
      //   ]
      // ];
      // $result = $client->__soapCall('getLocationsByGeoCriteria', [$param]);

      $result = $this->getStoresXml();

      $stores = $result->return->locations ?? '';
      $storesData = [];

      foreach ($stores ?? [] as $store) {
        $storesData[$store->locationExternalId] = [
          'locationExternalId' => $store->locationExternalId ?? '',
          'name' => $store->locationName ?? '',
          'address' => $store->companyAddress ?? '',
          'geocoordinates' => $store->geocoordinates ?? ''
        ];
      }

      return new JsonResponse($storesData);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting stores. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  private function getStoresXml() {
    $xml = '<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns2="http://services.timecommerce.timetrade.com/ws" xmlns:ns3="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:ns4="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <S:Header>
          <ns3:Security>
            <ns3:UsernameToken>
              <ns3:Username>bootsapiuser</ns3:Username>
              <ns3:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">jG4@dF0p</ns3:Password>
            </ns3:UsernameToken>
          </ns3:Security>
        </S:Header>
        <S:Body>
          <ns2:getLocationsByGeoCriteria>
            <locationSearchGeoCriteria>
              <latitude>25.0764689</latitude>
              <longitude>55.1403815</longitude>
              <radius>50</radius>
              <maxNumberOfLocations>500</maxNumberOfLocations>
              <unit>miles</unit>
            </locationSearchGeoCriteria>
          </ns2:getLocationsByGeoCriteria>
        </S:Body>
      </S:Envelope>';
    $wsdl   = "https://api-stage.timetradesystems.co.uk/soap/ConfigurationServices?wsdl";
    $client = new \SoapClient($wsdl, array(  'soap_version' => SOAP_1_1,
      'trace' => true,
    ));
    $soapBody = new \SoapVar($xml, \XSD_ANYXML);
    $return = $client->__SoapCall('getLocationsByGeoCriteria', array($soapBody));

    return $return;
  }

}
