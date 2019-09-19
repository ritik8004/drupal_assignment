<?php

namespace Drupal\acq_promotion;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
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
   * Qualified Promotion Node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $promotionNode;

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
  public function isApplicable() {
    $applicable = FALSE;
    // Fetch all promotions of this type and set qualified promotion node.
    return $applicable;
  }

  /**
   * Sets qualified promotion node.
   */
  protected function setPromotionNode() {
    // Collect all the eligible promotions for this plugin type.
    // Based on priority set promotion node.
  }

  /**
   * Gets specific promotions of this type.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getPromotions() {
    $nodeQuery = $this->entityTypeManager->getStorage('node')->getQuery();
    $nodeQuery->condition('type', AcqPromotionsManager::ACQ_PROMOTIONS_BUNDLE)
      ->condition('status', Node::PUBLISHED)
      ->condition('field_acq_promotion_type', 'cart')
      ->execute();
  }

  /**
   * Get qualified promotion node.
   *
   * @return \Drupal\node\NodeInterface
   *   Promotion node.
   */
  public function getPromotionNode() {
    // TODO: Validate promotion node has been fetched.
    return $this->promotionNode;
  }

  /**
   * {@inheritdoc}
   */
  public function getInactiveLabelThreshold() {
    // TODO: Implement getInactiveLabelThreshold() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLabelThreshold() {
    // TODO: Implement getActiveLabelThreshold() method.
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
