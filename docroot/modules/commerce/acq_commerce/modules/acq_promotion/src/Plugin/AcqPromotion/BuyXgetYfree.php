<?php

namespace Drupal\acq_promotion\Plugin\AcqPromotion;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_promotion\AcqPromotionBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Buy X Get Y Free cart level promotion.
 *
 * @ACQPromotion(
 *   id = "buy_x_get_y_cheapest_free",
 *   label = @Translation("Buy X Get Y Free (Cheapest one free)"),
 * )
 */
class BuyXgetYfree extends AcqPromotionBase {

  /**
   * EntityTypeManagerInterface Definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CartStorageInterface Definition.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * BuyXgetYfree constructor.
   *
   * @param array $configuration
   *   Configurations.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin Definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\acq_cart\CartStorageInterface $cartStorage
   *   Cart Storage.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entityTypeManager,
                              CartStorageInterface $cartStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->cartStorage = $cartStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('acq_cart.cart_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInactiveLabel() {
    // TODO: Implement getInactiveLabel() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLabel() {
    // TODO: Implement getActiveLabel() method.
  }

}
