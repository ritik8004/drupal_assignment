<?php

namespace Drupal\alshaya_product\Plugin\views\argument_default;

use Drupal\acq_promotion\AcqPromotionsManager;
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
   * Acq Promotion manager service.
   *
   * @var \Drupal\acq_promotion\AcqPromotionsManager
   */
  protected $acqPromotionsManager;

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
   * @param \Drupal\acq_promotion\AcqPromotionsManager $acqPromotionsManager
   *   The acq promotion manager service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_manager,
                              RouteMatchInterface $route_match,
                              AcqPromotionsManager $acqPromotionsManager) {
    $this->entityManager = $entity_manager;
    $this->routeMatch = $route_match;
    $this->acqPromotionsManager = $acqPromotionsManager;
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
      $container->get('acq_promotion.promotions_manager')
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
        $attached_skus = $this->acqPromotionsManager->getSkusForPromotion($node);
        foreach ($attached_skus as $sku) {
          $sku_text = $sku->getSku();
          $parent_sku = alshaya_acm_product_get_parent_sku_by_sku($sku_text);
          if ($parent_sku === NULL) {
            $skus[] = $sku_text;
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
