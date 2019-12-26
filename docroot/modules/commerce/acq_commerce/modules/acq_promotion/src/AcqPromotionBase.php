<?php

namespace Drupal\acq_promotion;

use Drupal\Core\Plugin\PluginBase;
use Drupal\node\NodeInterface;

/**
 * Class AcqPromotionBase.
 *
 * @package Drupal\acq_promotion
 */
abstract class AcqPromotionBase extends PluginBase implements AcqPromotionInterface {

  /**
   * NodeInterface Definition.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $promotionNode;

  /**
   * BuyXgetYfree constructor.
   *
   * @param array $configuration
   *   Configurations.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin Definition.
   * @param \Drupal\node\NodeInterface $promotionNode
   *   Promotion Node.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              NodeInterface $promotionNode) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->promotionNode = $promotionNode;
  }

  /**
   * {@inheritdoc}
   */
  public function getInactiveLabel() {
    return $this->promotionNode->get('field_acq_promotion_label')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLabel() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getPromotionCartStatus() {
    return NULL;
  }

}
