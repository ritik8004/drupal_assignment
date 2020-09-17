<?php

namespace Drupal\alshaya_aura\Helper;

/**
 * Class AuraStatus.
 */
final class AuraStatus {
  /**
   * No Linking and No data in MDC.
   */
  const APC_NOT_LINKED_NO_DATA = 0;

  /**
   * No Linking but Data in MDC.
   */
  const APC_NOT_LINKED_MDC_DATA = 1;

  /**
   * LINKED and VERIFIED Customers.
   */
  const APC_LINKED_VERIFIED = 2;

  /**
   * LINKED but not Verified Customers.
   */
  const APC_LINKED_NOT_VERIFIED = 3;

  /**
   * Not linked and No data in MDC.
   */
  const APC_NOT_LINKED_NOT_U = 4;

  /**
   * Return all the constant values in the class.
   *
   * @return array
   *   The constant values in the class.
   */
  public static function getAllAuraStatus() {
    $reflection_class = new \ReflectionClass(__CLASS__);
    return $reflection_class->getConstants();
  }

  /**
   * Get the values of statuses which are linked.
   *
   * @return array
   *   Returns those statuses which indicate linked status.
   */
  public static function getLinkedLoyaltyStatuses() {
    return [
      self::APC_LINKED_NOT_VERIFIED,
      self::APC_LINKED_VERIFIED,
    ];
  }

}
