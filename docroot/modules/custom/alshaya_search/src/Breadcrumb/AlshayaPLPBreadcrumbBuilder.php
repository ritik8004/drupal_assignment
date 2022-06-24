<?php

namespace Drupal\alshaya_search\Breadcrumb;

use Drupal\alshaya_main_menu\PathProcessor\PathProcessorPrettyPaths;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AlshayaPLPBreadcrumbBuilder.
 *
 * Code here is mostly similar to AlshayaPDPBreadcrumbBuilder, any change done
 * here must be checked for similar change required for PDP pages.
 */
class AlshayaPLPBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityRepository;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaPLPBreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Breadcrumb for 'plp' pages.
    if ($route_match->getRouteName() === 'entity.taxonomy_term.canonical' || $route_match->getRouteName() === 'alshaya_main_menu.category_view_all') {
      $term = $route_match->getParameter('taxonomy_term');
      if ($term->bundle() === 'acq_product_category') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();

    // Add the home page link. We need it always.
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));

    // Get the current page's taxonomy term from route params.
    $term = $route_match->getParameter('taxonomy_term');

    // Add the current page term to cache dependency.
    $breadcrumb->addCacheableDependency($term);

    // Get all parents of current term to show the heirarchy.
    $parents = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($term->id());

    /** @var \Drupal\taxonomy\Entity\Term $term */
    foreach (array_reverse($parents) as $term) {
      $term = $this->entityRepository->getTranslationFromContext($term);

      // Add the term to cache dependency.
      $breadcrumb->addCacheableDependency($term);

      $options = [];
      if (!$term->get('field_display_as_clickable_link')->getString() || $term->get('field_display_view_all')->getString()) {
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

    if (PathProcessorPrettyPaths::$isRequestViewAll) {
      $breadcrumb->addLink(Link::createFromRoute($this->t('View All'), '<none>'));
    }

    // Add the current route context in cache.
    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
