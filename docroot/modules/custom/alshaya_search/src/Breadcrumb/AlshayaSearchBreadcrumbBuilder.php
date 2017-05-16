<?php

namespace Drupal\alshaya_search\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaSearchBreadcrumbBuilder.
 */
class AlshayaSearchBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $parameters = $route_match->getParameters()->all();
    if (!empty($parameters['view_id'])) {
      return $parameters['view_id'] == 'search';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));

    $breadcrumb->mergeCacheMaxAge(0);
    $breadcrumb->addCacheableDependency(['url.path']);

    $queryString = explode('&', \Drupal::request()->getQueryString());

    // If on search page but no filter.
    if (empty($queryString[0])) {
      $breadcrumb->addLink(Link::createFromRoute(t('Search'), 'views.view.search'));
    }

    foreach ($queryString as $string) {
      $query = explode('=', $string);
      if ($query[0] == 'keywords') {
        $breadcrumb->addLink(Link::createFromRoute('Search results for "' . $query[1] . '"', 'views.view.search'));
        return $breadcrumb;
      }
    }
    return $breadcrumb;
  }

}
