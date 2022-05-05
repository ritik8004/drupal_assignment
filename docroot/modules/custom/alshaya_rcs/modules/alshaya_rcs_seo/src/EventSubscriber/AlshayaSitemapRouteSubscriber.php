<?php

namespace Drupal\alshaya_rcs_seo\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Perform redirection for image sitemap to MDC urls.
 */
class AlshayaSitemapRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('alshaya_image_sitemap.alshaya_image_sitemap_get_url')) {
      $route->setDefault('_controller', '\Drupal\alshaya_rcs_seo\Controller\AlshayaRcsImageSitemapController::redirectImageSiteMap');
    }
  }

}
