<?php

namespace Drupal\acq_commerce\Conductor;

use Acquia\Hmac\Exception\MalformedResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\RequestException;

/**
 * Trait AgentRequestTrait.
 *
 * @package Drupal\acq_commerce\Conductor
 *
 * @ingroup acq_commerce
 */
trait AgentRequestTrait {

  /**
   * Version of API.
   *
   * @var string
   */
  protected $apiVersion;

  /**
   * HTTP (Guzzle) Conductor Client Factory.
   *
   * @var ClientFactory
   */
  protected $clientFactory;

  /**
   * Debug / Verbose Connection Logging.
   *
   * @var bool
   */
  private $debug;

  /**
   * System / Watchdog Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * TryAgentRequest.
   *
   * Try a simple request with the Guzzle client, adding debug callbacks
   * and catching / logging request exceptions if needed.
   *
   * @param callable $doReq
   *   Request closure, passed client and opts array.
   * @param string $action
   *   Action name for logging.
   * @param string $reskey
   *   Result data key (or NULL)
   * @param string $acm_uuid
   *   The acm_uuid used to create the client.
   *
   * @return mixed
   *   API response.
   *
   * @throws \Exception
   */
  protected function tryAgentRequest(callable $doReq, $action, $reskey = NULL, $acm_uuid = "") {

    $client = $this->clientFactory->createClient($acm_uuid);
    $reqOpts = [];
    $logger = ($this->logger) ?: \Drupal::logger('acq_commerce');

    if ($this->debug) {
      $logger->info(sprintf('%s: Attempting Request.', $action));

      // Log transfer final endpoint and total time in debug mode.
      $reqOpts['on_stats'] =
        function (TransferStats $stats) use ($logger, $action) {
          $code =
            ($stats->hasResponse()) ?
              $stats->getResponse()->getStatusCode() :
              0;

          $logger->info(sprintf(
            '%s: Requested %s in %.4f [%d].',
            $action,
            $stats->getEffectiveUri(),
            $stats->getTransferTime(),
            $code
          ));
        };
    }

    if ($acm_uuid) {
      // This can be overridden in doReq function or using updateStoreContext.
      $reqOpts['query']['store_id'] = $acm_uuid;
    }
    else {
      // This can be overridden in doReq function or using updateStoreContext.
      $reqOpts['query']['store_id'] = $this->storeId;
    }

    // Make Request.
    try {
      /** @var \GuzzleHttp\Psr7\Response $result */
      $result = $doReq($client, $reqOpts);
    }
    catch (\Exception $e) {
      $class = get_class($e);

      $mesg = sprintf(
        '%s: %s during request: (%d) - %s',
        $action,
        $class,
        $e->getCode(),
        $e->getMessage()
      );

      $logger->error($mesg);

      if ($e->getCode() == 404
        || $e instanceof MalformedResponseException
        || $e instanceof ConnectException) {
        throw new \Exception(acq_commerce_api_down_global_error_message(), APIWrapper::API_DOWN_ERROR_CODE);
      }
      elseif ($e instanceof RequestException) {
        throw new ConductorException($mesg, $e->getCode(), $e);
      }
      else {
        throw $e;
      }
    }

    $response = json_decode($result->getBody(), TRUE);
    if (($response === NULL) || ($this->apiVersion === 'v1' && !isset($response['success']))) {
      $mesg = sprintf(
        '%s: Invalid / Unrecognized Conductor response: %s',
        $action,
        $result->getBody()
      );

      $logger->error($mesg);
      throw new ConductorException($mesg);
    }

    if ($this->apiVersion === 'v1' && !$response['success']) {
      $logger->info(sprintf(
        '%s: Conductor request unsuccessful: %s',
        $action,
        $result->getBody()
      ));

      // Process the response to check if error is downtime error
      // from Magento.
      $errors = [];
      if (preg_match('/response:(.*)/i', $result->getBody(), $errors)) {
        if (isset($errors[1])) {
          $error = json_decode(strtolower($errors[1]), TRUE);

          if (isset($error['status'])) {
            $error_code = (int) $error['status'];

            if ($error_code >= 500 && $error_code < 600) {
              throw new \Exception(acq_commerce_api_down_global_error_message(), APIWrapper::API_DOWN_ERROR_CODE);
            }
          }
        }
      }

      throw new ConductorResultException($response);
    }

    if ($this->apiVersion === 'v1' && strlen($reskey)) {
      if (!isset($response[$reskey])) {
        throw new ConductorResultException($response);
      }

      return ($response[$reskey]);
    }
    else {
      if ($this->debug) {
        $logger->debug("Response: " . nl2br(print_r($response, TRUE)));
      }
      return ($response);
    }
  }

}
