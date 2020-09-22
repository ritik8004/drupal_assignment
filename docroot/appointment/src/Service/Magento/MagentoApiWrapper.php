<?php

namespace App\Service\Magento;

use GuzzleHttp\TransferStats;
use Psr\Log\LoggerInterface;

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
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * MagentoInfo constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Service providing Magento info.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(MagentoInfo $magento_info,
                              LoggerInterface $logger) {
    $this->magentoInfo = $magento_info;
    $this->logger = $logger;
  }

  /**
   * Wrapper function to do Magento API request.
   *
   * @param string $method
   *   Request method.
   * @param string $url
   *   Request URL.
   * @param array $request_options
   *   Request options (optional).
   * @param string $action
   *   Action to log with stats. (optional).
   *
   * @return mixed
   *   Response data.
   *
   * @throws \Exception
   */
  public function doRequest(string $method, string $url, array $request_options = [], string $action = '') {
    $that = $this;

    $request_options['on_stats'] = function (TransferStats $stats) use ($that, $action) {
      $code = ($stats->hasResponse())
        ? $stats->getResponse()->getStatusCode()
        : 0;

      $that->logger->info(sprintf(
        'Finished API request %s in %.4f. Response code: %d. Method: %s. Action: %s',
        $stats->getEffectiveUri(),
        $stats->getTransferTime(),
        $code,
        $stats->getRequest()->getMethod(),
        $action
      ));
    };

    $request_options['timeout'] = $request_options['timeout'] ?? 30;
    try {
      $response = $this->magentoInfo->getMagentoApiClient()->request(
        $method,
        $this->magentoInfo->getMagentoUrl() . '/' . ltrim($url, '/'),
        $request_options
      );

      $result = $response->getBody()->getContents();

      $result = is_string($result) && !empty($result)
        ? json_decode($result, TRUE)
        : $result;

      if ($response->getStatusCode() !== 200) {
        $message = 'Error occurred while calling: ' . $url . ' result: ' . json_encode($result);
        $this->logger->error($message);
        return [
          'error' => TRUE,
          'error_message' => $message,
        ];
      }
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return [
        'error' => TRUE,
        'error_message' => $e->getMessage(),
      ];
    }

    return $result;
  }

}
