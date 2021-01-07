<?php

namespace App\Service\Magento;

use App\Service\CartErrorCodes;
use App\Service\Utility;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerInterface;

/**
 * Provides integration with Magento.
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
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(MagentoInfo $magento_info,
                              Utility $utility,
                              LoggerInterface $logger) {
    $this->magentoInfo = $magento_info;
    $this->utility = $utility;
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
        'Finished API request %s in %.4f. Response code: %d. Method: %s. Action: %s. X-Cache: %s; X-Cache-Hits: %s; X-Served-By: %s;',
        $stats->getEffectiveUri(),
        $stats->getTransferTime(),
        $code,
        $stats->getRequest()->getMethod(),
        $action,
        $stats->hasResponse() ? $stats->getResponse()->getHeaderLine('x-cache') : '',
        $stats->hasResponse() ? $stats->getResponse()->getHeaderLine('x-cache-hits') : '',
        $stats->hasResponse() ? $stats->getResponse()->getHeaderLine('x-served-by') : ''
      ));
    };

    $request_options['timeout'] = $request_options['timeout'] ?? $this->magentoInfo->getPhpTimeout('default');
    $request_options['headers']['Alshaya-Channel'] = 'web';
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

      if ($response->getStatusCode() > 500) {
        throw new \Exception('Back-end system is down', 600);
      }
      elseif ($action === 'native' && $response->getStatusCode() == 404) {
        throw new \Exception('', 404);
      }
      elseif ($response->getStatusCode() !== 200) {
        if (empty($result)) {
          $this->logger->error('Error while doing MDC api. Response result is empty. Status code: @code', [
            '@code' => $response->getStatusCode(),
          ]);
          throw new \Exception($this->utility->getDefaultErrorMessage(), 500);
        }
        elseif (!empty($result['message'])) {
          $message = $this->getProcessedErrorMessage($result);

          // Log the error message.
          $this->logger->error('Error while doing MDC api call. Error message: @message, Code: @result_code, Response code: @response_code.', [
            '@message' => $message,
            '@result_code' => $result['code'] ?? '-',
            '@response_code' => $response->getStatusCode(),
          ]);

          // The following case happens when there is a stock mismatch between
          // Magento and OMS.
          if (($response->getStatusCode() === 400)
            && (isset($result['code']))
            && ($result['code'] == CartErrorCodes::CART_CHECKOUT_QUANTITY_MISMATCH)
          ) {
            throw new \Exception($message, CartErrorCodes::CART_CHECKOUT_QUANTITY_MISMATCH);
          }

          throw new \Exception($message, 500);
        }
        elseif (!empty($result['messages']) && !empty($result['messages']['error'])) {
          $error = reset($result['messages']['error']);
          $this->logger->error('Error while doing MDC api call. Error message no empty. Error message: @message', [
            '@message' => $error['message'],
          ]);
          throw new \Exception($error['message'], $error['code']);
        }
      }
    }
    catch (ConnectException $e) {
      $this->logger->error($e->getMessage());
      throw new \Exception($this->utility->getDefaultErrorMessage(), 601);
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

    $parameters = $response['parameters'] ?? [];
    if (empty($parameters)) {
      return $message;
    }

    if (array_values($parameters) === $parameters) {
      return vsprintf($message, $parameters);
    }

    foreach ($parameters as $name => $value) {
      $message = str_replace("%$name", $value, $message);
    }

    return $message;
  }

  /**
   * Fetches and returns the Magento info service.
   *
   * @return \App\Service\Magento\MagentoInfo
   *   The magentoInfo service object.
   */
  public function getMagentoInfo() {
    return $this->magentoInfo;
  }

}
