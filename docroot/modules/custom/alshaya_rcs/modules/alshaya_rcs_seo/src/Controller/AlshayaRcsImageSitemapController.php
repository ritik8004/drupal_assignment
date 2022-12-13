<?php

namespace Drupal\alshaya_rcs_seo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Class Alshaya Rcs Image Sitemap Controller.
 */
class AlshayaRcsImageSitemapController extends ControllerBase {

  /**
   * Redirect Image Sitemap to MDC Url.
   */
  public function redirectImageSiteMap() {
    // Change the sitemap domain based on config value.
    $config = $this->config('alshaya_rcs_seo.settings');
    $sitemap_domain = $config->get('sitemap_domain_to_use');
    if ($sitemap_domain == 'magento') {
      $sitemap_domain_url = $this->config('alshaya_api.settings')->get('magento_host');
    }
    else {
      global $base_url;
      $sitemap_domain_url = $base_url;
    }

    $country_code = _alshaya_custom_get_site_level_country_code();
    $mdc_image_sitemap = $sitemap_domain_url . '/media/sitemap/' . strtolower($country_code) . '/image_sitemap.xml';
    // Redirect to MDC or Drupal domain.
    $response = new TrustedRedirectResponse($mdc_image_sitemap);
    $response->addCacheableDependency($config);
    return $response;
  }

}
