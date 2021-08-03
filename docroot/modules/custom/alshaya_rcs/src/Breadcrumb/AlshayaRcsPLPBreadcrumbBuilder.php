<?php

namespace Drupal\alshaya_rcs\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class to build the breadcrumb for RCS PLP pages.
 */
class AlshayaRcsPLPBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Breadcrumb for 'plp' pages.
    if ($route_match->getRouteName() == 'entity.taxonomy_term.canonical') {
      $term = $route_match->getParameter('taxonomy_term');
      if ($term->bundle() === 'rcs_category') {
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

    // Get the current page's taxonomy term from route params.
    $term = $route_match->getParameter('taxonomy_term');

    // Add the current page term to cache dependency.
    $breadcrumb->addCacheableDependency($term);

    // Add the home page link. We need it always.
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));

    $breadcrumb->addLink(Link::fromTextAndUrl('#rcs.categories.breadcrumbs.category_name#', Url::fromUserInput('#rcs.categories.breadcrumbs.category_url_path#')));

    // Add the current route context in cache.
    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
