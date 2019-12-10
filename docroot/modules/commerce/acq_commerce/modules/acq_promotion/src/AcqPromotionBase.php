<?php

namespace Drupal\acq_promotion;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Class AcqPromotionBase.
 *
 * @package Drupal\acq_promotion
 */
abstract class AcqPromotionBase extends PluginBase implements AcqPromotionInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

}
