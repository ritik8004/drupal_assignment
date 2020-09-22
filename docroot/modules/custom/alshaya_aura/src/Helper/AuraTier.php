<?php

namespace Drupal\alshaya_aura\Helper;

/**
 * Class AuraTier.
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
  const ALL_TIERS = [
    self::TIER_1 => 'TIER_1',
    self::TIER_2 => 'TIER_2',
    self::TIER_3 => 'TIER_3',
  ];

  const DEFAULT_TIER = 'TIER_1';

}
