<?php

namespace Drupal\acq_commerce\Conductor;

use Drupal\acq_commerce\Connector\ConnectorException;

/**
 * Class Conductor Result Exception.
 *
 * @package Drupal\acq_commerce\Conductor
 *
 * @ingroup acq_commerce
 */
class ConductorResultException extends ConnectorException {

  /**
   * Conductor Reported Success.
   *
   * @var bool
   */
  protected $success;

  /**
   * Conductor Results / Messages.
   *
   * @var array
   */
  protected $failures = [];

  /**
   * Constructor.
   *
   * @param array $result
   *   Conductor Result Data.
   */
  public function __construct(array $result) {

    $this->success = (isset($result['success'])) ? (bool) $result['success'] : FALSE;

    foreach ($result as $key => $mesg) {
      if ($key === 'success') {
        continue;
      }

      $prefix = 'response:';

      if (strpos($mesg, $prefix)) {
        $responseString = substr($mesg, strpos($mesg, $prefix) + strlen($prefix));
        $response = json_decode($responseString, TRUE);
        if (is_array($response) && isset($response['message'])) {
          $mesg = $response['message'];

          if (isset($response['parameters'])) {
            foreach ($response['parameters'] as $name => $value) {
              $mesg = str_replace("%$name", $value, $mesg);
            }
          }
        }
      }

      $this->failures[$key] = $mesg;
    }

    if ($this->success) {
      $mesg = 'Conductor request successful but did not contain requested data.';
    }
    else {
      // Generic exception message.
      $mesg = 'Conductor request unsuccessful.';

      // Check if we have a better exception message in failures.
      if ($this->failures) {
        // We return the first failure message in getMessage(), rest can be
        // accessed via getFailureMessage().
        $mesg = array_shift($this->failures);
      }
    }

    return parent::__construct($mesg);
  }

  /**
   * IsConductorSuccessful.
   *
   * If Conductor reported success on this request (this may happen if
   * success if reported but the result does not contain an expected key).
   *
   * @return bool
   *   If Conductor reported success on this request.
   */
  public function isConductorSuccessful() {
    return $this->success;
  }

  /**
   * GetFailureMessages.
   *
   * Get any failure messages returned with the Conductor request, generally
   * keyed by the name of the delegator that generated them.
   *
   * @return string[]
   *   Array with failures.
   */
  public function getFailureMessages() {
    return $this->failures;
  }

  /**
   * GetFailureMessage.
   *
   * Get a specific failure message (or a generic) returned with the
   * Conductor request. Key will generally be a specific Conductor
   * delegator that generated the error.
   *
   * @param string $key
   *   Conductor delegator identifier.
   *
   * @return string
   *   Specific message for $key.
   */
  public function getFailureMessage($key) {
    if (isset($this->failures[$key])) {
      return $this->failures[$key];
    }
    else {
      return sprintf('No message returned from %s.', $key);
    }
  }

}
