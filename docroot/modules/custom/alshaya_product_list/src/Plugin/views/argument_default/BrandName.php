<?php

namespace Drupal\alshaya_product_list\Plugin\views\argument_default;

use Drupal\Core\Path\PathValidatorInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Brand name default argument.
 *
 * @ViewsArgumentDefault(
 *   id = "brand_name",
 *   title = @Translation("Brand name from URL")
 * )
 */
class BrandName extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

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
   * BrandName constructor.
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
    // Rely on the Request object to get the brand name as views
    // arguments rather than Route matcher service. In case of AJAX requests
    // populating the facets, the arguments don't get populated leading to empty
    // facets on Brand detail page post AJAX request.
    if (($url_object = $this->pathValidator->getUrlIfValid($this->requestStack->getCurrentRequest()->getPathInfo())) &&
      ($url_object->getRouteName() == 'entity.node.canonical') &&
      ($node_id = $url_object->getRouteParameters()['node']) &&
      (($node = Node::load($node_id)) instanceof NodeInterface)) {
      return $node->get('field_brand_value')->first()->getString();
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
