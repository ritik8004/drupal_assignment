<?php

namespace App\Service\Magento;

use App\Service\Utility;

/**
 * Class MagentoApiWrapper.
 */
class MagentoApiWrapper {

  /**
   * Service providing Magento Info.
   *
   * @var \App\Service\Magento\MagentoInfo
   */
  protected $magentoInfo;

  /**
   * Utility.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * MagentoInfo constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Service providing Magento info.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(MagentoInfo $magento_info, Utility $utility) {
    $this->magentoInfo = $magento_info;
    $this->utility = $utility;
  }

  /**
   * Wrapper function to do Magento API request.
   *
   * @param string $method
   *   Request method.
   * @param string $url
   *   Request URL.
   * @param array $data
   *   Request data (optional).
   *
   * @return mixed
   *   Response data.
   *
   * @throws \Exception
   */
  public function doRequest(string $method, string $url, array $data = []) {
    $response = $this->magentoInfo->getMagentoApiClient()->request(
      $method,
      $this->magentoInfo->getMagentoUrl() . '/' . ltrim($url, '/'),
      $data
    );

    $result = $response->getBody()->getContents();

    $result = is_string($result) && !empty($result)
      ? json_decode($result, TRUE)
      : $result;

    if ($response->getStatusCode() > 500) {
      throw new \Exception('Back-end system is down', 600);
    }
    elseif ($response->getStatusCode() !== 200) {
      if (empty($result)) {
        throw new \Exception($this->utility->getDefaultErrorMessage(), 500);
      }
      elseif (!empty($result['message'])) {
        throw new \Exception($this->getProcessedErrorMessage($result), 500);
      }
      elseif (!empty($result['messages']) && !empty($result['messages']['error'])) {
        $error = reset($result['messages']['error']);
        throw new \Exception($error['message'], $error['code']);
      }
    }

    return $result;
  }

  /**
   * Wrapper function to process the response error message from Magento.
   *
   * @param array $response
   *   Response data.
   *
   * @return string
   *   Processed message.
   */
  protected function getProcessedErrorMessage(array $response) {
    $message = $response['message'];

    foreach ($response['parameters'] ?? [] as $name => $value) {
      $message = str_replace("%$name", $value, $message);
    }

    return $message;
  }

}
