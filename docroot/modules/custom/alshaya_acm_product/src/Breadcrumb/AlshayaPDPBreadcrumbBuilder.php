<?php

namespace Drupal\alshaya_acm_product\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\mysql\Driver\Database\mysql\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_acm_product\ProductCategoryHelper;

/**
 * Class AlshayaPDPBreadcrumbBuilder.
 *
 * Code here is mostly similar to AlshayaPLPBreadcrumbBuilder, any change done
 * here must be checked for similar change required for PLP pages.
 */
class AlshayaPDPBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The database service.
   *
   * @var \Drupal\mysql\Driver\Database\mysql\Connection
   */
  protected $connection;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Entity Repository service object.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Entity Type Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Path Validator service object.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The Title Resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Request stock service object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Product Category Helper service object.
   *
   * @var \Drupal\alshaya_acm_product\ProductCategoryHelper
   */
  protected $productCategoryHelper;

  /**
   * AlshayaPDPBreadcrumbBuilder constructor.
   *
   * @param \Drupal\mysql\Driver\Database\mysql\Connection $connection
   *   Database service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The lnaguage manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   Path Validator service object.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The Title Resolver.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stock service object.
   * @param \Drupal\alshaya_acm_product\ProductCategoryHelper $product_category_helper
   *   Product Category Helper service object.
   */
  public function __construct(Connection $connection,
                              LanguageManagerInterface $language_manager,
                              EntityRepositoryInterface $entity_repository,
                              EntityTypeManagerInterface $entity_type_manager,
                              ModuleHandlerInterface $module_handler,
                              PathValidatorInterface $path_validator,
                              TitleResolverInterface $title_resolver,
                              RequestStack $request_stack,
                              ProductCategoryHelper $product_category_helper) {
    $this->connection = $connection;
    $this->languageManager = $language_manager;
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->pathValidator = $path_validator;
    $this->titleResolver = $title_resolver;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->productCategoryHelper = $product_category_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Breadcrumb for 'pdp' pages.
    if ($route_match->getRouteName() == 'entity.node.canonical') {
      return $route_match->getParameter('node')->bundle() == 'acq_product';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb_cache_tags = [];
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));

    /** @var \Drupal\node\Entity\Node $node */
    $node = $route_match->getParameter('node');

    if ($field_category = $node->get('field_category')) {
      $term_list = $field_category->getValue();
      $breadcrumb_cache_tags = [];
      // Build cache dependency on all terms this Product is tagged with. We
      // need to make the breadcrumb dependent on all categories product is
      // tagged with since, enabling/disabling the category on MDC would make it
      // dependent on new categories.
      foreach ($term_list as $term) {
        $breadcrumb_cache_tags[] = 'taxonomy_term:' . $term['target_id'];
      }
      if ($parents = $this->productCategoryHelper->getBreadcrumbTermList($term_list)) {
        foreach (array_reverse($parents) as $term) {
          $term = $this->entityRepository->getTranslationFromContext($term);

          $breadcrumb->addCacheableDependency($term);

          $options = [];
          if (!$term->get('field_display_as_clickable_link')->getString()) {
            // Make term link non-clickable.
            $options = [
              'attributes' => [
                'class' => ['no-link'],
              ],
            ];
          }
          // Remove term name from breadcrumb if checked for any category.
          $remove_term_from_breadcrumb = $term->get('field_remove_term_in_breadcrumb')->getString();
          if (!$remove_term_from_breadcrumb) {
            // Add term to breadcrumb.
            $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()], $options));
          }
        }
      }
    }

    // This breadcrumb builder is based on a route parameter, and hence it
    // depends on the 'route' cache context.
    $breadcrumb->addCacheContexts(['route']);

    $request = $this->currentRequest;
    $url_object = $this->pathValidator->getUrlIfValid($request->getPathInfo());
    $route_name = $url_object->getRouteName();

    // Don't invoke when this service is requested from a non-PDP page
    // explicitly.
    if ($route_name === 'entity.node.canonical') {
      $title = $this->titleResolver->getTitle($request, $route_match->getRouteObject());
      $breadcrumb->addLink(Link::createFromRoute($title, 'entity.node.canonical', ['node' => $node->id()]));
    }

    // Cacheability data of the node.
    $breadcrumb_cache_tags[] = 'node:' . $node->id();
    $breadcrumb->addCacheTags($breadcrumb_cache_tags);

    return $breadcrumb;
  }

}
