<?php

namespace Drupal\alshaya_rcs_seo\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * RCS SEO route subscriber class.
 */
class AlshayaRcsSeoRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Perform redirection for image sitemap to MDC urls.
    if ($route = $collection->get('alshaya_image_sitemap.alshaya_image_sitemap_get_url')) {
      $route->setDefault('_controller', '\Drupal\alshaya_rcs_seo\Controller\AlshayaRcsImageSitemapController::redirectImageSiteMap');
    }

    if ($route = $collection->get('alshaya_seo.sitemap')) {
      $route->setDefault('_controller', '\Drupal\alshaya_rcs_seo\Controller\AlshayaRcsSeoController::sitemap');
    }
  }

}
