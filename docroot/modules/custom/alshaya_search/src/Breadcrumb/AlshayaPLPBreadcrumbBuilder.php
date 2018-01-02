<?php

namespace Drupal\alshaya_search\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class AlshayaPLPBreadcrumbBuilder.
 *
 * Code here is mostly similar to AlshayaPDPBreadcrumbBuilder, any change done
 * here must be checked for similar change required for PDP pages.
 */
class AlshayaPLPBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Breadcrumb for 'plp' pages.
    return $route_match->getRouteName() == 'entity.taxonomy_term.canonical';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    /** @var \Drupal\Core\Entity\EntityRepository $entityRepository */
    $entityRepository = \Drupal::service('entity.repository');

    $breadcrumb = new Breadcrumb();

    // Add the home page link. We need it always.
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));

    // Get the current page's taxonomy term from route params.
    $term = $route_match->getParameter('taxonomy_term');

    // Add the current page term to cache dependency.
    $breadcrumb->addCacheableDependency($term);

    // Get all parents of current term to show the heirarchy.
    $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($term->id());

    $alshaya_department_pages = [];

    // Check if alshaya department page module is enabled.
    if (\Drupal::moduleHandler()->moduleExists('alshaya_department_page')) {
      $alshaya_department_pages = alshaya_department_page_get_pages();
    }

    /** @var \Drupal\taxonomy\Entity\Term $term */
    foreach (array_reverse($parents) as $term) {
      $term = $entityRepository->getTranslationFromContext($term);

      // Add the term to cache dependency.
      $breadcrumb->addCacheableDependency($term);

      // Check if current term has department page available.
      if (isset($alshaya_department_pages[$term->id()])) {
        $nid = $alshaya_department_pages[$term->id()];

        // We use department page link instead of PLP link.
        /** @var \Drupal\node\Entity\Node $node */
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

        if ($node->isPublished()) {
          // Get the translated node.
          $node = $entityRepository->getTranslationFromContext($node);

          // Add department page to breadcrumb.
          $breadcrumb->addLink(Link::createFromRoute(_alshaya_department_page_get_node_title($node), 'entity.node.canonical', ['node' => $node->id()]));

          // Add the node to cache dependency.
          $breadcrumb->addCacheableDependency($node);
        }
      }
      else {
        // Add term to breadcrumb.
        $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]));
      }
    }

    // Add the current route context in cache.
    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
