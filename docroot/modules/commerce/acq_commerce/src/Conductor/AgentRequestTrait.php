<?php

namespace Drupal\acq_commerce\Conductor;

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
  private $apiVersion;

  /**
   * HTTP (Guzzle) Conductor Client Factory.
   *
   * @var ClientFactory
   */
  private $clientFactory;

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
  private $logger;

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
   *
   * @return mixed
   *   API response.
   *
   * @throws ConductorException
   * @throws ConductorResultException
   */
  protected function tryAgentRequest(callable $doReq, $action, $reskey = NULL) {

    $client = $this->clientFactory->createClient();
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

    // Make Request.
    try {
      $result = $doReq($client, $reqOpts);
    }
    catch (RequestException $e) {
      $mesg = sprintf(
        '%s: Exception during request: (%d) - %s',
        $action,
        $e->getCode(),
        $e->getMessage()
      );

      $logger->error($mesg);
      throw new ConductorException($mesg, $e->getCode(), $e);
    }

    $response = json_decode($result->getBody()->getContents(), TRUE);
    if (($response === NULL) || (!isset($response['success']))) {
      $mesg = sprintf(
        '%s: Invalid / Unrecognized Conductor response: %s',
        $action,
        $result->getBody()->getContents()
      );

      $logger->error($mesg);
      throw new ConductorException($mesg);
    }

    if (!$response['success']) {
      $logger->info(sprintf(
        '%s: Conductor request unsuccessful: %s',
        $action,
        $result->getBody()->getContents()
      ));

      throw new ConductorResultException($response);
    }

    if (strlen($reskey)) {
      if (!isset($response[$reskey])) {
        throw new ConductorResultException($response);
      }

      return ($response[$reskey]);
    }
    else {
      return ($response);
    }
  }

}
