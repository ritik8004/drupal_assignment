<?php

namespace App\Controller;

use App\Cache\Cache;
use App\Translation\TranslationHelper;
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
   * Cache Client.
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
   * @param \App\Cache\Cache $cache
   *   Cache Helper.
   * @param \App\Translation\TranslationHelper $translationHelper
   *   Translation Helper.
   */
  public function __construct(LoggerInterface $logger,
                              APIHelper $api_helper,
                              XmlAPIHelper $xml_api_helper,
                              Helper $helper,
                              Cache $cache,
                              TranslationHelper $translationHelper) {
    $this->logger = $logger;
    $this->apiHelper = $api_helper;
    $this->xmlApiHelper = $xml_api_helper;
    $this->helper = $helper;
    $this->cache = $cache;
    $this->serviceUrl = $this->apiHelper->getTimetradeBaseUrl() . APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL;
    $this->translationHelper = $translationHelper;
  }

  /**
   * Get Programs.
   *
   * @return json
   *   Program data from API.
   */
  public function getPrograms(Request $request) {
    // Get Programs from cache.
    try {
      $langcode = $request->query->get('langcode');
      $item = $this->cache->getItem('programs', $langcode);
      if ($item) {
        return new JsonResponse($item);
      }
    }
    catch (\ErrorException $e) {
      $this->logger->error('Error occurred while getting programs from cache. Message: @message', [
        '@message' => $e->getMessage(),
      ]);
    }

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
            'name' => $this->translationHelper->getTranslation($program->programName, $langcode),
          ];
        }
      }

      // Set cache.
      $this->cache->setItem('programs', $programData, $langcode);

      return new JsonResponse($programData);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting programs. Message: @message', [
        '@message' => $e->getMessage(),
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
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
      $langcode = $request->query->get('langcode');
      $locationExternalId = $this->apiHelper->getlocationExternalIds();
      $locationExternalId = is_array($locationExternalId) ? reset($locationExternalId) : $locationExternalId;
      $program = $request->query->get('program');

      if (empty($locationExternalId) || empty($program)) {
        throw new \Exception('locationExternalId and program is required to get activities.');
      }

      // Get Activities from cache.
      $item = $this->cache->getItem($program . '_activities', $langcode);
      if ($item) {
        return new JsonResponse($item);
      }

      $param = [
        'locationExternalId' => $locationExternalId,
        'programExternalId' => $program,
      ];
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $result = $client->__soapCall('getActivities', [$param]);
      $activities = $result->return->activities;
      $activityData = [];

      foreach ($activities ?? [] as $activity) {
        if ($activity->isEnabled) {
          $activityData[] = [
            'id' => $activity->activityExternalId,
            'name' => $this->translationHelper->getTranslation($activity->activityName, $langcode),
            'description' => $this->translationHelper->getTranslation($activity->description, $langcode),
          ];
        }
      }

      // Set activities cache.
      $this->cache->setItem($program . '_activities', $activityData, $langcode);

      return new JsonResponse($activityData);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting activities. Message: @message', [
        '@message' => $e->getMessage(),
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
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

      $langcode = $requestQuery->get('langcode');
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

          $storesData[] = [
            'locationExternalId' => $storeId ?? '',
            'name' => $this->translationHelper->getTranslation($store->locationName, $langcode) ?? '',
            'address' => $this->getAddressTranslation($store->companyAddress, $langcode) ?? '',
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
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
    }
  }

  /**
   * Get Stores Schedules.
   *
   * @return array
   *   Stores Schedules from API.
   */
  public function getStoreSchedule($storeId) {
    // Get store schedules from cache.
    $item = $this->cache->getItem('store_' . $storeId);
    if ($item) {
      return $item;
    }

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

      $this->cache->setItem('store_' . $storeId, $weeklySchedulesData);

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
      $langcode = $request->query->get('langcode');
      $locationExternalId = $request->query->get('location');
      if (empty($request->query->get('location'))) {
        $message = 'Location ID is required to get store details.';

        throw new \Exception($message);
      }

      $item = $this->cache->getItem('location_' . $locationExternalId, $langcode);
      if ($item) {
        return new JsonResponse($item);
      }

      $param = [
        'locationSearchCriteria' => [
          'locationExternalId' => $locationExternalId,
          'locationGroupId' => '',
          'exactMatchOnly' => TRUE,
        ],
      ];
      $client = $this->apiHelper->getSoapClient($this->serviceUrl);
      $result = $client->__soapCall('getLocationsByCriteria', [$param]);

      // Add translations.
      $this->getAddressTranslation(
        $result->return->locations->companyAddress,
        $langcode
      );

      $this->cache->setItem('location_' . $locationExternalId, $result, $langcode);
      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while fetching location details. Message: @message', [
        '@message' => $e->getMessage(),
      ]);
      $error = $this->apiHelper->getErrorMessage($e->getMessage(), $e->getCode());

      return new JsonResponse($error, 400);
    }
  }

  /**
   * Translates Store address.
   */
  public function getAddressTranslation(&$address, $langcode) {
    foreach ($address as &$item) {
      $item = $this->translationHelper->getTranslation($item, $langcode);
    }
  }

}
