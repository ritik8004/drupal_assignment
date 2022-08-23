<?php

namespace Drupal\alshaya_aura_react\Constants;

/**
 * Contains aura dictionary api configs.
 */
final class AuraDictionaryApiConstants {
  /**
   * Aura accrual ratio constant.
   */
  public const CASHBACK_ACCRUAL_RATIO = 'APC_CASHBACK_ACCRUAL_RATIO';

  /**
   * Aura redemption ratio constant.
   */
  public const CASHBACK_REDEMPTION_RATIO = 'APC_CASHBACK_REDEMPTION_RATIO';

  /**
   * Aura recognition accrual ratio constant.
   */
  public const RECOGNITION_ACCRUAL_RATIO = 'RECOGNITION_ACCRUAL_RATIO';

  /**
   * Aura phone number prefix constant.
   */
  public const EXT_PHONE_PREFIX = 'EXT_PHONE_PREFIX';

  /**
   * Aura tier types constant.
   */
  public const APC_TIER_TYPES = 'APC_TIER_TYPES';

  /**
   * Aura brands constant.
   */
  public const APC_BRANDS = 'APC_BRANDS';

  /**
   * Contains all the dictionary api keys constants in the class.
   */
  public const ALL_DICTIONARY_API_CONSTANTS = [
    'CASHBACK_ACCRUAL_RATIO' => self::CASHBACK_ACCRUAL_RATIO,
    'CASHBACK_REDEMPTION_RATIO' => self::CASHBACK_REDEMPTION_RATIO,
    'RECOGNITION_ACCRUAL_RATIO' => self::RECOGNITION_ACCRUAL_RATIO,
    'EXT_PHONE_PREFIX' => self::EXT_PHONE_PREFIX,
    'APC_TIER_TYPES' => self::APC_TIER_TYPES,
    'APC_BRANDS' => self::APC_BRANDS,
  ];

}
