<?php

namespace Drupal\alshaya_pdp_layouts\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class PreprocessMagazineEvent.
 *
 * @package Drupal\alshaya_pdp_layouts
 */
class PreprocessMagazineEvent extends Event {

  const EVENT_NAME = 'preprocess_alshaya_magazine';

  /**
   * PreprocessMagazineEvent constructor.
   *
   * @param array $variables
   *   Variables array for the current page.
   */
  public function __construct(array $variables) {
    $this->variables = $variables;
  }

  /**
   * Get the preprocess array of the current page.
   *
   * @return array
   *   Variables array.
   */
  public function getVariables() {
    return $this->variables;
  }

  /**
   * Update value.
   *
   * @param array $variables
   *   Updated value.
   */
  public function setVariables(array $variables) {
    $this->variables = $variables;
  }

}
