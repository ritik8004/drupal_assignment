<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Service\SoapClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\APIHelper;
use App\Helper\XmlAPIHelper;
use App\Helper\Helper;

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
   * @param \App\Service\SoapClient $client
   *   Soap client service.
   * @param \App\Helper\APIHelper $api_helper
   *   API Helper.
   * @param \App\Helper\XmlAPIHelper $xml_api_helper
   *   Xml API Helper.
   * @param \App\Helper\Helper $helper
   *   Helper.
   */
  public function __construct(LoggerInterface $logger,
                              SoapClient $client,
                              APIHelper $api_helper,
                              XmlAPIHelper $xml_api_helper,
                              Helper $helper) {
    $this->logger = $logger;
    $this->client = $client;
    $this->apiHelper = $api_helper;
    $this->xmlApiHelper = $xml_api_helper;
    $this->helper = $helper;
  }

  /**
   * Get Available time slots.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Time slot data from API.
   */
  public function getTimeSlots(Request $request) {
    try {
      $result = $this->xmlApiHelper->fetchTimeSlots($request);

      return new JsonResponse($result);

    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while getting time slots from TT API. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

}
