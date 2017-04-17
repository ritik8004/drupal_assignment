<?php

namespace Drupal\alshaya_product\Plugin\views\argument_default;

use Drupal\taxonomy\TermInterface;
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
 *   id = "product_category_term_id",
 *   title = @Translation("Product category term ID(s) from URL")
 * )
 */
class ProductCategoryTermId extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

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
    // Load default argument from taxonomy page.
    if (($taxonomy_term = $this->routeMatch->getParameter('taxonomy_term')) && $taxonomy_term instanceof TermInterface) {
      $terms = [];
      $storage = $this->entityManager->getStorage('taxonomy_term');
      $term_items = $storage->loadTree($taxonomy_term->getVocabularyId(), $taxonomy_term->id(), NULL, TRUE);
      $terms[] = $taxonomy_term->id();
      if (!empty($term_items)) {
        // Loop to get children term names.
        foreach ($term_items as $term_item) {
          $terms[] = $term_item->id();
        }
      }
      return implode('+', $terms);
    }
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
