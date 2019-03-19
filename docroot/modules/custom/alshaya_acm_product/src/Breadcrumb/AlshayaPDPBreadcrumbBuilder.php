<?php

namespace Drupal\alshaya_acm_product\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * @var \Drupal\Core\Database\Driver\mysql\Connection
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
   * AlshayaPDPBreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\Database\Driver\mysql\Connection $connection
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
   */
  public function __construct(Connection $connection,
                              LanguageManagerInterface $language_manager,
                              EntityRepositoryInterface $entity_repository,
                              EntityTypeManagerInterface $entity_type_manager,
                              ModuleHandlerInterface $module_handler,
                              PathValidatorInterface $path_validator,
                              TitleResolverInterface $title_resolver,
                              RequestStack $request_stack) {
    $this->connection = $connection;
    $this->languageManager = $language_manager;
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->pathValidator = $path_validator;
    $this->titleResolver = $title_resolver;
    $this->currentRequest = $request_stack->getCurrentRequest();
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
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));

    /* @var \Drupal\node\Entity\Node $node */
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

      $term_list = $this->filterEnabled($term_list);
      $inner_term = $this->termTreeGroup($term_list);

      if ($inner_term) {
        $parents = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($inner_term);

        foreach (array_reverse($parents) as $term) {
          $term = $this->entityRepository->getTranslationFromContext($term);

          $breadcrumb->addCacheableDependency($term);

          // Add term to breadcrumb.
          if (empty($term->get('field_display_as_clickable_link')->getString())) {
            // Make term link non-clickable.
            $breadcrumb->addLink(Link::createFromRoute($term->getName(), '<none>'));
          }
          else {
            // Make term link clickable.
            $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]));
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

  /**
   * Get only enabled terms.
   *
   * @param array $terms
   *   Terms array.
   *
   * @return array
   *   Filtered terms.
   */
  protected function filterEnabled(array $terms = []) {
    // Remove disabled terms.
    foreach ($terms as $index => $row) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($row['target_id']);

      if ($term instanceof TermInterface && $term->get('field_commerce_status')->getString()) {
        continue;
      }

      // If term not found or not enabled, we unset it.
      unset($terms[$index]);
    }

    return array_values($terms);
  }

  /**
   * Get most inner term for the first group.
   *
   * @param array $terms
   *   Terms array.
   *
   * @return int
   *   Term id.
   */
  public function termTreeGroup(array $terms = []) {
    if (!empty($terms)) {
      $root_group = $this->getRootGroup($terms[0]['target_id']);
      $root_group_terms = [];
      foreach ($terms as $term) {
        $root = $this->getRootGroup($term['target_id']);
        if ($root == $root_group) {
          $root_group_terms[] = $term['target_id'];
        }
      }

      return $this->getInnerDepthTerm($root_group_terms);
    }

    return NULL;

  }

  /**
   * Get the root level parent tid of a given term.
   *
   * @param int $tid
   *   Term id.
   *
   * @return int
   *   Root parent term id.
   */
  protected function getRootGroup($tid) {
    $static = &drupal_static('alshaya_pdp_breadcrumb_builder_get_root_group', []);

    if (isset($static[$tid])) {
      return $static[$tid];
    }

    // Recursive call to get parent root parent tid.
    while ($tid > 0) {
      $query = $this->connection->select('taxonomy_term__parent', 'tth');
      $query->fields('tth', ['parent_target_id']);
      $query->condition('tth.entity_id', $tid);
      $parent = $query->execute()->fetchField();
      if ($parent == 0) {
        $static[$tid] = $tid;
        return $tid;
      }

      $tid = $parent;
    }
  }

  /**
   * Get the most inner term term based on the depth.
   *
   * @param array $terms
   *   Array of term ids.
   *
   * @return int
   *   The term id.
   */
  protected function getInnerDepthTerm(array $terms = []) {
    if (empty($terms)) {
      return NULL;
    }

    $static = &drupal_static('alshaya_pdp_breadcrumb_builder_get_root_group', []);
    $term_ids = implode(',', $terms);
    if (isset($static[$term_ids])) {
      return $static[$term_ids];
    }

    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    $depths = $this->connection->select('taxonomy_term_field_data', 'ttfd')
      ->fields('ttfd', ['tid', 'depth_level'])
      ->condition('ttfd.tid', $terms, 'IN')
      ->condition('ttfd.langcode', $current_langcode)
      ->execute()->fetchAllKeyed();

    // Flip key/value.
    $terms = array_flip($terms);
    // Merge two array (overriding depth value).
    $depths = array_replace($terms, $depths);
    // Get all max values and get first one.
    $max_depth = array_keys($depths, max($depths));
    $most_inner_tid = $max_depth[0];

    $static[$term_ids] = $most_inner_tid;

    return $most_inner_tid;
  }

}
