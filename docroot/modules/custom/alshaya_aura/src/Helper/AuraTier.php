<?php

namespace Drupal\alshaya_aura\Helper;

/**
 * Class AuraTier.
 *
 * @todo Update the constant names once available from MDC. The values should
 * remain the same. Also getDefaultAuraTier() needs to be modified.
 */
final class AuraTier {
  /**
   * Tier 0 and the default constant.
   */
  const APC_TIER_0 = 0;

  /**
   * Tier 1 constant.
   */
  const APC_TIER_1 = 1;

  /**
   * Tier 2 constant.
   */
  const APC_TIER_2 = 2;

  /**
   * Returns all the tiers in this class in an array format.
   *
   * @return array
   *   Array of all tiers in the class, grouped by the tier value.
   */
  public static function getAllAuraTiers() {
    $reflection_class = new \ReflectionClass(__CLASS__);
    return $reflection_class->getConstants();
  }

  /**
   * Returns value of the default tier constant.
   *
   * @return array
   *   The default tier value.
   */
  public static function getDefaultAuraTier() {
    return self::APC_TIER_0;
  }

}
