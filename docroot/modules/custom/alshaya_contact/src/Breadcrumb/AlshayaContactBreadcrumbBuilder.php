<?php

namespace Drupal\alshaya_contact\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaContactBreadcrumbBuilder.
 */
class AlshayaContactBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    // Breadcrumb for the 'alshaya_contact' page.
    $webform = $attributes->getParameter('webform');
    if (is_object($webform)) {
      return $webform->id() == 'alshaya_contact';
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $webform = $route_match->getParameter('webform');
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($webform->label(), 'entity.webform.canonical', ['webform' => $webform->id()]));
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
