<?php

namespace Drupal\alshaya_aura_react\Helper;

/**
 * Contains aura dictionary api configs.
 */
final class AuraDictionaryApiConstants {
  /**
   * Aura accrual ratio constant.
   */
  const CASHBACK_ACCRUAL_RATIO = 'APC_CASHBACK_ACCRUAL_RATIO';

  /**
   * Aura redemption ratio constant.
   */
  const CASHBACK_REDEMPTION_RATIO = 'APC_CASHBACK_REDEMPTION_RATIO';

  /**
   * Aura phone number prefix constant.
   */
  const EXT_PHONE_PREFIX = 'EXT_PHONE_PREFIX';

  /**
   * Contains all the dictionary api keys constants in the class.
   */
  const ALL_DICTONARY_API_CONSTANTS = [
    'CASHBACK_ACCRUAL_RATIO' => self::CASHBACK_ACCRUAL_RATIO,
    'CASHBACK_REDEMPTION_RATIO' => self::CASHBACK_REDEMPTION_RATIO,
    'EXT_PHONE_PREFIX' => self::EXT_PHONE_PREFIX,
  ];

}
