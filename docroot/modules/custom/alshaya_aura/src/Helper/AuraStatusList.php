<?php

namespace Drupal\alshaya_aura\Helper;

/**
 * Class AuraStatusList.
 */
final class AuraStatusList {

  /**
   * Method to get AURA Status values.
   *
   * @return array
   *   Array of all aura status.
   */
  public static function getAllAuraStatus() {
    // @TODO: Will remove the hardcoded values once we have the actual data.
    $auraStatusValues = [
      'linked_verified' => 'Linked Verified',
      'linked_not_verified' => 'Linked Not verified',
      'not_linked' => 'Not Linked',
    ];

    return $auraStatusValues;
  }

}
