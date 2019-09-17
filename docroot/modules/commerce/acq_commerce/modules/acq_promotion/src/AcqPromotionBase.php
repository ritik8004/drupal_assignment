<?php

namespace Drupal\acq_promotion;

use Drupal\Core\Plugin\PluginBase;

/**
 * Class AcqPromotionBase.
 *
 * @package Drupal\acq_promotion
 */
abstract class AcqPromotionBase extends PluginBase implements AcqPromotionInterface {

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority() {
    return self::ACQ_PROMOTION_DEFAULT_PRIORITY;
  }

}
