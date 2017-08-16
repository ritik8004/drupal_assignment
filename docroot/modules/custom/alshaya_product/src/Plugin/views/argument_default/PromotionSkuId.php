<?php

namespace Drupal\alshaya_product\Plugin\views\argument_default;

use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Product category term name default argument.
 *
 * @ViewsArgumentDefault(
 *   id = "promotion_sku_id",
 *   title = @Translation("SKU Id(s) from promotion")
 * )
 */
class PromotionSkuId extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Alshaya Promotion manager service.
   *
   * @var AlshayaPromotionsManager
   */
  protected $promotionsManager;

  /**
   * ProductCategoryTermId constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin configuration.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param AlshayaPromotionsManager $promotionsManager
   *   The acq promotion manager service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_manager,
                              RouteMatchInterface $route_match,
                              AlshayaPromotionsManager $promotionsManager) {
    $this->entityManager = $entity_manager;
    $this->routeMatch = $route_match;
    $this->promotionsManager = $promotionsManager;
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
      $container->get('current_route_match'),
      $container->get('alshaya_acm_promotion.manager')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Database\InvalidQueryException
   * @throws \InvalidArgumentException
   */
  public function getArgument() {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    $skus = [];

    if (($node = $this->routeMatch->getParameter('node')) && $node instanceof Node) {
      if ($node->bundle() === 'acq_promotion') {
        $skus = $this->promotionsManager->getSkutextsForPromotion($node);
      }
    }

    return implode('+', $skus);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['url'];
  }

}
