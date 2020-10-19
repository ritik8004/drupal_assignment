<?php

namespace Drupal\alshaya_contact\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Alshaya Contact Breadcrumb Builder.
 */
class AlshayaContactBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

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
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($webform->label(), 'entity.webform.canonical', ['webform' => $webform->id()]));
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
