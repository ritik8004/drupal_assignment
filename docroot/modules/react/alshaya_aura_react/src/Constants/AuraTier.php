<?php

namespace Drupal\alshaya_aura_react\Constants;

/**
 * Contains all aura tier constants.
 *
 * @todo Update the constant values once available from MDC.
 */
final class AuraTier {
  /**
   * Tier 1 and the default constant.
   */
  const TIER_1 = 1;

  /**
   * Tier 2 constant.
   */
  const TIER_2 = 2;

  /**
   * Tier 3 constant.
   */
  const TIER_3 = 3;

  /**
   * Contains all the tiers in the class.
   */
  const ALL_AURA_TIERS = [
    'TIER_1' => self::TIER_1,
    'TIER_2' => self::TIER_2,
    'TIER_3' => self::TIER_3,
  ];

  const DEFAULT_TIER = 'TIER_1';

}
