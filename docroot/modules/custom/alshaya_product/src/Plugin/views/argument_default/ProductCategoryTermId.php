<?php

namespace Drupal\alshaya_product\Plugin\views\argument_default;

use Drupal\Core\Path\PathValidatorInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The Path Validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

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
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_manager,
                              PathValidatorInterface $pathValidator,
                              RequestStack $requestStack) {
    $this->entityManager = $entity_manager;
    $this->pathValidator = $pathValidator;
    $this->requestStack = $requestStack;
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
      $container->get('path.validator'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    // Rely on the Request object to get the taxonomy term ids as views
    // arguments rather than Route matcher service. In case of AJAX requests
    // populating the facets, the arguments don't get populated leading to empty
    // facets on PLP/Promotion detail page post AJAX request.
    if (($url_object = $this->pathValidator->getUrlIfValid($this->requestStack->getCurrentRequest()->getPathInfo())) &&
      ($url_object->getRouteName() == 'entity.taxonomy_term.canonical') &&
      ($taxonomy_tid = $url_object->getRouteParameters()['taxonomy_term']) &&
      (($taxonomy_term = Term::load($taxonomy_tid)) instanceof TermInterface)) {
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
