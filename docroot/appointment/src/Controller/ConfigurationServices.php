<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Service\SoapClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ConfigurationServices.
 */
class ConfigurationServices {

  /**
   * WSDL configuration service url.
   */
  const WSDL_CONFIGURATION_SERVICES_URL = 'https://api-stage.timetradesystems.co.uk/soap/ConfigurationServices?wsdl';

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
   * ConfigurationServices constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\SoapClient $client
   *   Soap client service.
   */
  public function __construct(LoggerInterface $logger,
                              SoapClient $client) {
    $this->logger = $logger;
    $this->client = $client;
  }

  /**
   * Get Location External Id.
   *
   * @return string
   *   Location External Id.
   */
  private function getlocationExternalId() {
    $client = $this->client->getSoapClient(self::WSDL_CONFIGURATION_SERVICES_URL);
    $param = ['locationGroupExtId' => 'Boots'];

    if (empty($client)) {
      $this->logger->error('Empty soap client.');
    }

    try {
      $result = $client->__soapCall('getLocationGroup', [$param]);
      $locationExternalId = $result->return->locationGroup->locationExternalIds;
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting locationExternalId. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }

    return $locationExternalId;
  }

  /**
   * Get Programs.
   *
   * @return json
   *   Program data from API.
   */
  public function getPrograms() {
    $client = $this->client->getSoapClient(self::WSDL_CONFIGURATION_SERVICES_URL);
    $param = ['locationExternalId' => $this->getlocationExternalId()];

    if (empty($client)) {
      $this->logger->error('Empty soap client.');
    }

    try {
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

    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting programs. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }

    return new JsonResponse($programData);
  }

  /**
   * Get Programs.
   *
   * @return json
   *   Program data from API.
   */
  public function getActivities(Request $request) {
    $client = $this->client->getSoapClient(self::WSDL_CONFIGURATION_SERVICES_URL);
    $program = $request->query->get('program');
    $param = [
      'locationExternalId' => $this->getlocationExternalId(),
      'programExternalId' => $program,
    ];

    if (empty($client)) {
      $this->logger->error('Empty soap client.');
    }

    try {
      $result = $client->__soapCall('getActivities', [$param]);
      $activities = $result->return->activities;
      $activityData = [];

      foreach ($activities ?? [] as $activity) {
        if ($activity->isEnabled) {
          $activityData[] = [
            'id' => $activity->activityExternalId,
            'name' => $activity->activityName,
            'description' => $activity->activityName . ' SOnam',
          ];
        }
      }

    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting activities. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }

    return new JsonResponse($activityData);
  }

}
