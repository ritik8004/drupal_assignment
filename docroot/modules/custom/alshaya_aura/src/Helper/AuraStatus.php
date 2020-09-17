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

}
