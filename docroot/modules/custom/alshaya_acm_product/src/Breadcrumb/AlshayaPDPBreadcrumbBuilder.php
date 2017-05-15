<?php

namespace Drupal\alshaya_acm_product\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaPDPBreadcrumbBuilder.
 */
class AlshayaPDPBreadcrumbBuilder implements BreadcrumbBuilderInterface {

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
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));
    /* @var \Drupal\node\Entity\Node $node */
    $node = $route_match->getParameter('node');
    if ($field_category = $node->get('field_category')) {
      $category = $field_category->first()->getValue();
      $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($category['target_id']);
      foreach (array_reverse($parents) as $term) {
        $term = \Drupal::service('entity.repository')->getTranslationFromContext($term);
        $breadcrumb->addCacheableDependency($term);
        $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]));
      }

      // This breadcrumb builder is based on a route parameter, and hence it
      // depends on the 'route' cache context.
      $breadcrumb->addCacheContexts(['route']);
    }

    $request = \Drupal::request();
    $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
    $breadcrumb->addLink(Link::createFromRoute($title, 'entity.node.canonical', ['node' => $node->id()]));

    return $breadcrumb;
  }

}
