<?php

namespace Drupal\acq_promotion;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AcqPromotionBase.
 *
 * @package Drupal\acq_promotion
 */
abstract class AcqPromotionBase extends PluginBase implements AcqPromotionInterface, ContainerFactoryPluginInterface {

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
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->promotionNode = $configuration['promotion_node'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInactiveLabel() {
    return $this->promotionNode->get('field_acq_promotion_label')->getString();
  }

}
