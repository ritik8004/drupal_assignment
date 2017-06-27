<?php

namespace Drupal\alshaya_acm_promotion\Plugin\views\argument_default;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_manager, RouteMatchInterface $route_match) {
    $this->entityManager = $entity_manager;
    $this->routeMatch = $route_match;
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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    $skus = [];

    if (($node = $this->routeMatch->getParameter('node')) && $node instanceof Node) {
      if ($node->bundle() === 'acq_promotion') {
        $sku_ids = $node->get('field_acq_promotion_sku')->getValue();
        foreach ($sku_ids as $sku_entity_id) {
          $sku_id = alshaya_acm_product_get_sku_by_entity_id($sku_entity_id['target_id']);
          $parent_sku = alshaya_acm_product_get_parent_sku_by_sku($sku_id);
          if ($parent_sku === NULL) {
            $skus[] = $sku_id;
          }
          else {
            $skus[] = $parent_sku->getSku();
          }
        }
      }
    }

    // Filter out duplicate skus.
    $skus = array_unique($skus);

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
