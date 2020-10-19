<?php

namespace Drupal\alshaya_department_page\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Alshaya Department Page Breadcrumb Builder.
 */
class AlshayaDepartmentPageBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Breadcrumb for department pages.
    return $route_match->getRouteName() == 'entity.node.canonical'
    && $route_match->getParameter('node')->bundle() == 'department_page';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $node = $route_match->getParameter('node');
    $title = _alshaya_department_page_get_node_title($node);
    $breadcrumb->addLink(Link::createFromRoute($title, 'entity.node.canonical', ['node' => $node->id()]));
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
