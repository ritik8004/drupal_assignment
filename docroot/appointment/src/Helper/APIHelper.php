<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;
use App\Service\SoapClient;
use App\Service\Config\SystemSettings;

/**
 * Class APIHelper.
 *
 * @package App\Helper
 */
class APIHelper {
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
   * @param \App\Service\SoapClient $client
   *   Soap client service.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   */
  public function __construct(LoggerInterface $logger,
                              SoapClient $client,
                              SystemSettings $settings) {
    $this->logger = $logger;
    $this->client = $client;
    $this->settings = $settings;
  }

  /**
   * Get Location External Ids.
   *
   * @return string
   *   Location External Ids.
   */
  public function getlocationExternalIds() {
    try {
      $client = $this->client->getSoapClient(APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL);

      $appointment_settings = $this->settings->getSettings('appointment_settings');
      $param = ['locationGroupExtId' => $appointment_settings['location_group_ext_id']];

      $result = $client->__soapCall('getLocationGroup', [$param]);
      $locationExternalIds = $result->return->locationGroup ? $result->return->locationGroup->locationExternalIds : [];

      return $locationExternalIds;
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting locationExternalIds. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

}
