<?php

namespace App\Service;

use App\Service\Config\SystemSettings;
use Psr\Log\LoggerInterface;

/**
 * Class SoapClient.
 */
class SoapClient {

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * LoggerInterface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * SoapClient constructor.
   *
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(
    SystemSettings $settings,
    LoggerInterface $logger
  ) {
    $this->settings = $settings;
    $this->logger = $logger;
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

        $this->logger->log($message);
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

}
