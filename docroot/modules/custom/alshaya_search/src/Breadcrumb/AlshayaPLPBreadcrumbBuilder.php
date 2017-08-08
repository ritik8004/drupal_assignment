<?php

namespace Drupal\alshaya_search\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaPLPBreadcrumbBuilder.
 */
class AlshayaPLPBreadcrumbBuilder implements BreadcrumbBuilderInterface {

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
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));

    // Get the current page's taxonomy term from route params.
    $term = $route_match->getParameter('taxonomy_term');

    // Add the current page term to cache dependency.
    $breadcrumb->addCacheableDependency($term);

    // Get all parents of current term to show the heirarchy.
    $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($term->id());
    $alshaya_department_pages = alshaya_department_page_get_pages();
    /** @var \Drupal\taxonomy\Entity\Term $term */
    foreach (array_reverse($parents) as $term) {

      // Check if alshaya department page module is enabled.
      if (\Drupal::moduleHandler()->moduleExists('alshaya_department_page')) {

        // Check if current term has department page available.
        if (isset($alshaya_department_pages[$term->id()])) {
          $nid = $alshaya_department_pages[$term->id()];
          // We use department page link instead of PLP link.
          $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

          // Get the translated node.
          $node = $entityRepository->getTranslationFromContext($node);

          // Add link to breadcrumb.
          $breadcrumb->addLink(Link::createFromRoute(_alshaya_department_page_get_node_title($node), 'entity.node.canonical', ['node' => $node->id()]));

          // Add the node to cache dependency.
          $breadcrumb->addCacheableDependency($node);

          continue;
        }
      }

      // Get the translated term.
      $term = \Drupal::service('entity.repository')->getTranslationFromContext($term);

      // Add link to breadcrumb.
      $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]));

      // Add the term to cache dependency.
      $breadcrumb->addCacheableDependency($term);
    }

    // Add the current route context in cache.
    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
