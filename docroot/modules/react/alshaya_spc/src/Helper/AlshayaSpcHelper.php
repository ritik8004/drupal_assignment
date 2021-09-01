<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Site\Settings;

/**
 * Class containing general helper methods for SPC.
 */
class AlshayaSpcHelper {

  /**
   * Gets the commerce backend version.
   *
   * @return string
   *   The commerce backend verion.
   */
  public function getCommerceBackendVersion() {
    return Settings::get('commerce_backend')['version'];
  }

}
