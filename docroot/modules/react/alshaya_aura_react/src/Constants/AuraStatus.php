<?php

namespace Drupal\alshaya_aura_react\Constants;

/**
 * Constains all aura status constants.
 */
final class AuraStatus {
  /**
   * No Linking and No data in MDC.
   */
  const APC_NOT_LINKED_NO_DATA = 0;

  /**
   * No Linking but Data is present.
   */
  const APC_NOT_LINKED_DATA = 1;

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
   * Contains all the aura status in the class.
   */
  const ALL_AURA_STATUS = [
    'APC_NOT_LINKED_NO_DATA' => self::APC_NOT_LINKED_NO_DATA,
    'APC_NOT_LINKED_DATA' => self::APC_NOT_LINKED_DATA,
    'APC_LINKED_VERIFIED' => self::APC_LINKED_VERIFIED,
    'APC_LINKED_NOT_VERIFIED' => self::APC_LINKED_NOT_VERIFIED,
    'APC_NOT_LINKED_NOT_U' => self::APC_NOT_LINKED_NOT_U,
  ];

}
