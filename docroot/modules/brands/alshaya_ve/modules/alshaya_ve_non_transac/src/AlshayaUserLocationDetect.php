<?php

namespace Drupal\alshaya_ve_non_transac;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AlshayaUserLocationDetect.
 */
class AlshayaUserLocationDetect {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger channel factory instance.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $logger;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaAcmConfigCheck constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger, RequestStack $request_stack) {
    $this->configFactory = $config_factory;
    $this->logger = $logger->get('alshaya_ve_non_transac');
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * Helper function to get user's browsed country.
   */
  public function getUserCountryCode() {
    $latLong = $this->getLatLongFromCookie();
    if (empty($latLong->lat) || empty($latLong->long)) {
      return $this->getAllStoresCountry();
    }
    $countryCode = $this->getClientsBrowsedCountryCode($latLong->lat, $latLong->long);

    if ($countryCode) {
      $countryCode = strtolower($countryCode);
      if (in_array($countryCode, $this->getAllStoresCountry())) {
        return $countryCode;
      }
      else {
        return $this->getAllStoresCountry();
      }
    }
    return $this->getAllStoresCountry();
  }

  /**
   * Helper function to get latitute, longitute from cookies.
   */
  private function getLatLongFromCookie() {
    $latLong = [];
    $latLong['lat'] = $this->currentRequest->cookies->get('alshaya_client_latitude');
    $latLong['long'] = $this->currentRequest->cookies->get('alshaya_client_longitude');
    return (object) $latLong;
  }

  /**
   * Helper function to get country code.
   *
   * @param bool $lat
   *   Latitude.
   * @param bool $long
   *   Longitude.
   *
   * @return string
   *   Country code.
   */
  private function getClientsBrowsedCountryCode($lat, $long) {
    if (empty($this->configFactory->get('geolocation.settings')->get('google_map_api_key')) || empty($lat) || empty($long)) {
      return FALSE;
    }

    $countryCode = '';
    $apiKey = $this->configFactory->get('geolocation.settings')->get('google_map_api_key');

    $geocodeApiUrl = "https://maps.googleapis.com/maps/api/geocode/json?key=" . $apiKey . "&latlng=" . $lat . "," . $long;
    try {
      $geocodeData = file_get_contents($geocodeApiUrl);
      $geocodeData = json_decode($geocodeData);
      if (($geocodeData->status) && ($geocodeData->status == 'OK')) {
        if ($geocodeData) {
          $countryCode = $this->extractCountryCodeByGeocode($geocodeData);
        }
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Exception while invoking API @geoapi. Message: @message.', [
        '@geoapi' => $geocodeApiUrl,
        '@message' => $e->getMessage(),
      ]);
    }
    return $countryCode;
  }

  /**
   * Helper function to get short country code.
   *
   * @param object $geocodeData
   *   Geocode data object.
   *
   * @return string
   *   Country code.
   */
  private function extractCountryCodeByGeocode($geocodeData) {
    $countryCode = '';
    if (isset($geocodeData->results)) {
      if (isset($geocodeData->results[0])) {
        if (isset($geocodeData->results[0]->address_components)) {
          foreach ($geocodeData->results[0]->address_components as $value) {
            if (isset($value->types)) {
              if (isset($value->types[0])) {
                if ($value->types[0] == 'country') {
                  $countryCode = $value->short_name;
                }
              }
            }
          }
        }
      }
    }
    return $countryCode;
  }

  /**
   * Helper function to get list of countries used in store form.
   *
   * @return array
   *   Country list array.
   */
  private function getAllStoresCountry() {
    $countryFieldValues = FieldStorageConfig::loadByName('node', 'field_store_country')->getSetting('allowed_values');
    unset($countryFieldValues['All']);
    return array_keys($countryFieldValues);
  }

}
