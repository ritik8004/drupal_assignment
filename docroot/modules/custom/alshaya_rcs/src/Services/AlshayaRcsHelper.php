<?php

namespace Drupal\alshaya_rcs\Services;

use Drupal\Core\Site\Settings;

/**
 * General Helper service for the Ashaya RCS feature.
 */
class AlshayaRcsHelper {

  /**
   * Detects if the RCS replacement on PDP is enabled or not.
   *
   * @return bool
   *   Whether rcs replacement on pdp is enabled or not.
   */
  public function isRcsPdpEnabled() {
    return (bool) Settings::get('rcs_pdp_enabled');
  }

}
