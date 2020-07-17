<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\APIHelper;
use App\Helper\APIServicesUrls;
use App\Helper\XmlAPIHelper;
use App\Helper\Helper;

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
   * APIHelper.
   *
   * @var \App\Helper\APIHelper
   */
  protected $apiHelper;

  /**
   * XmlAPIHelper.
   *
   * @var \App\Helper\XmlAPIHelper
   */
  protected $xmlApiHelper;

  /**
   * Helper.
   *
   * @var \App\Helper\Helper
   */
  protected $helper;

  /**
   * ConfigurationServices constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Helper\APIHelper $api_helper
   *   API Helper.
   * @param \App\Helper\XmlAPIHelper $xml_api_helper
   *   Xml API Helper.
   * @param \App\Helper\Helper $helper
   *   Helper.
   */
  public function __construct(LoggerInterface $logger,
                              APIHelper $api_helper,
                              XmlAPIHelper $xml_api_helper,
                              Helper $helper) {
    $this->logger = $logger;
    $this->apiHelper = $api_helper;
    $this->xmlApiHelper = $xml_api_helper;
    $this->helper = $helper;
    $this->serviceUrl = $this->apiHelper->getTimetradeBaseUrl() . APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL;
  }

  /**
   * Get Programs.
   *
   * @return json
   *   Program data from API.
   */
  public function getPrograms() {
    try {
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $locationExternalId = $this->apiHelper->getlocationExternalIds();
      $locationExternalId = is_array($locationExternalId) ? reset($locationExternalId) : $locationExternalId;

      if (empty($locationExternalId)) {
        throw new \Exception('locationExternalId is required to get programs.');
      }

      $param = ['locationExternalId' => $locationExternalId];
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
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $locationExternalId = $this->apiHelper->getlocationExternalIds();
      $locationExternalId = is_array($locationExternalId) ? reset($locationExternalId) : $locationExternalId;
      $program = $request->query->get('program');

      if (empty($locationExternalId) || empty($program)) {
        throw new \Exception('locationExternalId and program is required to get activities.');
      }

      $param = [
        'locationExternalId' => $locationExternalId,
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
    try {
      $requestQuery = $request->query;

      $latitude = $requestQuery->get('latitude') ?? '';
      $longitude = $requestQuery->get('longitude') ?? '';
      $radius = $requestQuery->get('radius') ?? '';
      $maxLocations = $requestQuery->get('max-locations') ?? '';
      $unit = $requestQuery->get('unit') ?? '';

      $param = [
        'latitude' => $latitude,
        'longitude' => $longitude,
        'radius' => $radius,
        'maxLocations' => $maxLocations,
        'unit' => $unit,
      ];

      if (empty($latitude) || empty($longitude) || empty($radius) || empty($maxLocations) || empty($unit)) {
        $message = 'Required parameters missing to get stores.';
        $this->logger->error($message . ' Data: @request_data', [
          '@request_data' => json_encode($param),
        ]);
        throw new \Exception($message);
      }

      $locationExternalIds = $this->apiHelper->getlocationExternalIds();

      if (empty($locationExternalIds)) {
        // If no location is available in location group then we don't
        // try to get location by geo criteria and return empty array.
        return [];
      }

      $result = $this->xmlApiHelper->fetchStores($param);
      $stores = $result->return->locations ?? [];
      $storesData = [];

      foreach ($stores as $store) {
        $storeId = $store->locationExternalId;
        if (in_array($storeId, $locationExternalIds)) {
          $storeTiming = $this->getStoreSchedule($storeId);
          $storeLat = $store->geocoordinates->latitude ?? '';
          $storeLng = $store->geocoordinates->longitude ?? '';

          if (!empty($storeLat) && !empty($storeLng)) {
            $distanceInMiles = $this->helper->distance($latitude, $longitude, $storeLat, $storeLng, $unit);
          }

          // @TODO: Separate out distance from store data as when we cache
          // store info later, distance shouldn't be cached
          $storesData[] = [
            'locationExternalId' => $storeId ?? '',
            'name' => $store->locationName ?? '',
            'address' => $store->companyAddress ?? '',
            'lat' => $storeLat,
            'lng' => $storeLng,
            'storeTiming' => $storeTiming ?? '',
            'distanceInMiles' => $distanceInMiles ?? '',
          ];
        }
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

  /**
   * Get Stores Schedules.
   *
   * @return array
   *   Stores Schedules from API.
   */
  public function getStoreSchedule($storeId) {
    try {
      if (empty($storeId)) {
        throw new \Exception('storeId is required to get store schedule.');
      }
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $param = [
        'scheduleSearchCriteria' => [
          'locationExternalId' => $storeId,
        ],
      ];
      $result = $client->__soapCall('getLocationSchedulesByCriteria', [$param]);
      $weeklySchedules = $result->return->locationSchedules->weeklySubSchedule->weeklySubSchedulePeriods ?? [];
      $weeklySchedulesData = $this->helper->groupStoreTimings($weeklySchedules);

      return $weeklySchedulesData;
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting stores schedules. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  /**
   * Gets Store details by passing locationExternalId.
   */
  public function getStoreDetailsById(Request $request) {
    try {
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $locationExternalId = $request->query->get('location');
      if (empty($request->query->get('location'))) {
        $message = 'Location ID is required to get store details.';

        throw new \Exception($message);
      }

      $param = [
        'locationSearchCriteria' => [
          'locationExternalId' => $locationExternalId,
          'locationGroupId' => '',
          'exactMatchOnly' => TRUE,
        ],
      ];
      $result = $client->__soapCall('getLocationsByCriteria', [$param]);
      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching location details. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

}
