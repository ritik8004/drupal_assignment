<?php

namespace Drupal\acq_commerce\Conductor;

use Drupal\acq_commerce\Connector\ConnectorException;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\RequestException;

/**
 * Trait Ingest Request Trait.
 *
 * @package Drupal\acq_commerce\Conductor
 *
 * @ingroup acq_commerce
 */
trait IngestRequestTrait {

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
   * @var LoggerInterface
   */
  private $logger;

  /**
   * TryIngestRequest.
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
   *   Acm uuid.
   *
   * @throws \Drupal\acq_commerce\Connector\ConnectorException
   */
  protected function tryIngestRequest(callable $doReq, $action, $reskey = NULL, $acm_uuid = '') {

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

    // Make Request.
    try {
      $doReq($client, $reqOpts);
    }
    catch (RequestException $e) {
      $mesg = sprintf(
        '%s: Exception during request: (%d) - %s',
        $action,
        $e->getCode(),
        $e->getMessage()
      );

      $logger->error($mesg);
      throw new ConnectorException($mesg, $e->getCode(), $e);
    }
  }

}
