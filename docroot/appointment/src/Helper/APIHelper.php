<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;
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
   * Get Location External Ids.
   *
   * @return string
   *   Location External Ids.
   */
  public function getlocationExternalIds() {
    try {
      $client = $this->getSoapClient($this->getTimetradeBaseUrl() . APIServicesUrls::WSDL_CONFIGURATION_SERVICES_URL);

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

  /**
   * Get SoapClient object.
   *
   * @param string $wsdl
   *   The wsdl url for the soap client.
   *
   * @return client
   *   Return SoapClient object.
   */
  public function getSoapClient($wsdl) {
    try {
      $appointment_settings = $this->settings->getSettings('appointment_settings');
      $username = $appointment_settings['username'] ?? '';
      $password = $appointment_settings['password'] ?? '';

      if (empty($username) || empty($password)) {
        $message = 'Time trade credentials are not set.';

        $this->logger->error($message);
        throw new \Exception($message);
      }

      $headerNS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
      $passwordNS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText';

      // Create soap vars for username and password.
      $usernameNode = new \SoapVar('<ns2:Username>' . $username . '</ns2:Username>', XSD_ANYXML);
      $passwordNode = new \SoapVar('<ns2:Password Type="' . $passwordNS . '">' . $password . '</ns2:Password>', XSD_ANYXML);

      // Create Username token node and add vars.
      $usernameTokenNode = new \SoapVar([$usernameNode, $passwordNode], SOAP_ENC_OBJECT, NULL, $headerNS, 'UsernameToken', $headerNS);

      // Create security node.
      $securityNode = new \SoapVar([$usernameTokenNode], SOAP_ENC_OBJECT, NULL, $headerNS, 'Security', $headerNS);

      // Create a header with all above data.
      $header = [new \SoapHeader($headerNS, 'Security', $securityNode, TRUE)];

      // Create your SoapClient and add header to client.
      $client = new \SoapClient($wsdl, []);
      $client->__setSoapHeaders($header);

      return $client;
    }
    catch (\Exception $e) {
      $this->logger->error('Error occurred while creating SoapClient. Message: @message', [
        '@message' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  /**
   * Update/Insert Client.
   *
   * @param string $type
   *   Type of service to get the base url.
   *
   * @return string
   *   Base URL.
   */
  public function getTimetradeBaseUrl($type = 'appointment') {
    $appointment_settings = $this->settings->getSettings('appointment_settings');
    $baseUrl = $appointment_settings['timetrade_api_base_url'];

    if ($type === 'translation') {
      $baseUrl = $appointment_settings['timetrade_translation_base_url'];
    }

    if (empty($baseUrl)) {
      throw new \Exception('Timetrade base URL is not set.');
    }

    return $baseUrl;
  }

  /**
   * Provides clientExternalId from emailaddress.
   */
  public function checkifBelongstoUser($email) {
    $param = [
      'criteria' => [
        'emailAddress' => $email,
        'exactMatchOnly' => TRUE,
        'hideDisabled' => TRUE,
      ],
    ];

    $url = $this->getTimetradeBaseUrl() . APIServicesUrls::WSDL_CLIENT_SERVICES_URL;
    $client = $this->getSoapClient($url);
    $clientData = $client->__soapCall('getClientsByCriteria', [$param]);
    if (!property_exists($clientData->return, 'clients')) {
      return FALSE;
    }

    return $clientData->return->clients->clientExternalId;

  }

  /**
   * Provides error message array.
   */
  public function getErrorMessage() {
    $error = [
      'error' => TRUE,
      'error_message' => 'Something went wrong. Please try again.',
    ];

    return $error;
  }

}
