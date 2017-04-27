<?php

namespace Drupal\alshaya_search;

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
  public function applies(RouteMatchInterface $attributes) {
    $parameters = $attributes->getParameters()->all();
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

    $queryString = explode('&', \Drupal::request()->getQueryString());
    foreach ($queryString as $string) {
      $query = explode('=', $string);
      if ($query[0] == 'keywords') {
        $breadcrumb->addLink(Link::createFromRoute('Search results for "' . $query[1] . '"', '<none>'));
        return $breadcrumb;
      }
    }
    return $breadcrumb;
  }

}
