<?php

namespace Drupal\alshaya_seo_transac\Plugin\simple_sitemap\SitemapGenerator;

use Drupal\Core\Url;
use Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator\DefaultSitemapGenerator;

/**
 * Class AlshayaSitemapGenerator.
 *
 * @SitemapGenerator(
 *   id = "alshaya_sitemap_generator",
 *   label = @Translation("Alshaya sitemap generator"),
 * )
 */
class AlshayaSitemapGenerator extends DefaultSitemapGenerator {

  /**
   * Returns the sitemap link according to the required format.
   *
   *   The new format for sitemap links should be:
   *   /sitemap.xml, /sitemap-1.xml, /sitemap-2.xml and so on.
   *
   * @param int|null $delta
   *   The delta value.
   *
   * @return string
   *   The URL string.
   */
  public function getSitemapUrl($delta = NULL) {
    $url = $this->isDefaultVariant()
      ? Url::fromRoute(
        'simple_sitemap.sitemap_default',
        [],
        $this->getSitemapUrlSettings()
      )
      : Url::fromRoute(
        'simple_sitemap.sitemap_variant',
        [] + ['variant' => $this->sitemapVariant],
        $this->getSitemapUrlSettings()
      );

    return is_null($delta)
      ? $url->toString()
      : preg_replace('/sitemap\.xml/', "sitemap-$delta.xml", $url->toString());
  }

}
