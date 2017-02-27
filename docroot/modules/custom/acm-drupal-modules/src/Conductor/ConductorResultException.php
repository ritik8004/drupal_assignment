<?php
/**
 * @file
 * Contains Drupal\acq_commerce\Conductor\ConductorResultException
 */

namespace Drupal\acq_commerce\Conductor;

/**
 * Class ConductorResultException
 * @package Drupal\acq_commerce\Conductor
 * @ingroup acq_commerce
 */
class ConductorResultException extends ConductorException {

  /**
   * Conductor Reported Success
   * @var bool $sucess
   */
  protected $sucess;

  /**
   * Conductor Results / Messages
   * @var array $failures
   */
  protected $failures = array();

  /**
   * Constructor
   *
   * @param array $result Conductor Result Data
   *
   * @return void
   */
  public function __construct(array $result)
  {
    $this->success = (isset($result['success'])) ? (bool) $result['success'] : FALSE;

    foreach ($result as $key => $mesg) {
      if ($key === 'success') {
        continue;
      }

      $this->failures[$key] = $mesg;
    }

    if ($this->success) {
      $mesg = 'Conductor request successful but did not contain requested data.';
    } else {
      $mesg = 'Conductor request unsuccessful.';
    }

    return parent::__construct($mesg);
  }

  /**
   * isConductorSuccessful
   *
   * If Conductor reported success on this request (this may happen if
   * success if reported but the result does not contain an expected key).
   *
   * @return bool $success
   */
  public function isConductorSuccessful()
  {
    return $this->success;
  }

  /**
   * getFailureMessages
   *
   * Get any failure messages returned with the Conductor request, generally
   * keyed by the name of the delegator that generated them.
   *
   * @return string[] $failures
   */
  public function getFailureMessages()
  {
    return $this->failures;
  }

  /**
   * getFailureMessage
   *
   * Get a specific failure message (or a generic) returned with the
   * Conductor request. Key will generally be a specific Conductor
   * delegator that generated the error.
   *
   * @param string $key
   *
   * @return string $mesg
   */
  public function getFailureMessage($key)
  {
    if (isset($this->failures[$key])) {
      return $this->failures[$key];
    } else {
      return sprintf('No message returned from %s.', $key);
    }
  }

}
