<?php

namespace Drupal\alshaya_stores_finder\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaStoresFinderBreadcrumbBuilder.
 */
class AlshayaStoresFinderBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    $parameters = $attributes->getParameters()->all();
    // If store finder view.
    if (!empty($parameters['view_id'])) {
      return $parameters['view_id'] == 'stores_finder';
    }
    elseif (!empty($parameters['node'])) {
      // If store finder node page.
      return is_object($parameters['node']) && $parameters['node']->bundle() == 'store';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute(t('Find stores'), 'view.stores_finder.page_2'));

    /* @var \Drupal\node\Entity\Node $node */
    if ($node = $route_match->getParameter('node')) {
      if ($node->bundle() == 'store') {
        $breadcrumb->addLink(Link::createFromRoute($node->getTitle(), 'entity.node.canonical', ['node' => $node->id()]));
      }
    }

    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
