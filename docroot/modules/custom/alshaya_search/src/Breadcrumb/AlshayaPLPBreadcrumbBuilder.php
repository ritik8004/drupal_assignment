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
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));
    $term = $route_match->getParameter('taxonomy_term');
    $breadcrumb->addCacheableDependency($term);
    $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($term->id());
    foreach (array_reverse($parents) as $term) {
      $term = \Drupal::service('entity.repository')->getTranslationFromContext($term);
      $breadcrumb->addCacheableDependency($term);
      $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]));
    }

    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
